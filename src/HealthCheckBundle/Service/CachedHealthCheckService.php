<?php

namespace FT\HealthCheckBundle\Service;

use Exception;
use Monolog\Logger;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class CachedHealthCheckService
{
    const CACHE_POOL_SERVICE_PARAMETER_ID = "health_check.cache_pool";

    const CACHE_POOL_PREFIX = 'health_check.cache.executed.';

    /**
     * Gives the minimum ttl for cache pools so that unexpected cache misses do not happen when using the psr-6 compliant cache pool
     */
    const CACHE_INTERVAL_MINIMUM_TTL = 120;

    /**
     * Cache service
     *
     * @var CacheItemPoolInterface
     */
    protected $cache;
    
    /**
     * Application container
     *
     * @var Container
     */
    protected $container;

    /**
     * Logger service
     *
     * @var Logger
     */
    protected $logger;

    /**
     * An array of cache keys that need purging during this execution cycle
     *
     * @var string[]
     */
    protected $healthChecksToPurge;

    /**
     * @param Logger $logger
     * @param Pool $cache
     */
    public function __construct(Logger $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->healthChecksToPurge = [];
    }

    /**
     * Sets the cache pool id from the service ID
     *
     * @throws Exception
     * @throws ServiceNotFoundException
     * @param string $serviceId
     * @return void
     */
    public function setCachePoolFromServiceId(string $serviceId)
    {
        // In the event that the cache service has not been set default to keeping the cache service disabled
        if ($serviceId === "") {
            return;
        }

        if (!$this->container->has($serviceId)) {
            throw new ServiceNotFoundException("Error expected value of " . self::CACHE_POOL_SERVICE_PARAMETER_ID . "to be an exsisting service. Service ID does not exsist.");
        }

        $cacheService = $this->container->get($serviceId);
        
        if (!$cacheService instanceof CacheItemPoolInterface) {
            throw new Exception("Expected cache service to be Compatible with PSR-6 CacheItemPoolInterface.");
        }

        $this->cache = $cacheService;
    }

    /**
     * Executes a given healthCheck handler. Loads response from cache if health check runs on interval.
     *
     * @param HealthCheckHandlerInterface $healthCheckHandle
     * @return HealthCheck
     */
    public function runHealthCheckHandle(HealthCheckHandlerInterface $healthCheckHandle)
    {
        //If we don't cache the health check or the cache is not available just run the check.
        if (is_null($healthCheckHandle->getHealthCheckInterval()) || is_null($this->cache)) {
            return $healthCheckHandle->runHealthCheck();
        }

        try {
            //Try to get the health check from cache
            $cacheItem = $this->getHealthCheckFromCache($healthCheckHandle);

            $healthCheck = $cacheItem->get();

            $healthCheckInterval = $healthCheckHandle->getHealthCheckInterval();

            //If this healthCheck is not cached
            if ($this->healthCheckExpired($healthCheck, $healthCheckInterval)) {

                //Run the health check
                $healthCheck = $healthCheckHandle->runHealthCheck();

                // Make sure that the healthcheck will be properly cleaned out before we finish execution
                $this->queueHealthCheckToBePurged($healthCheckHandle);

                // Set it up so that the health check should be stored once they have all executed
                $this->queueHealthCheckToBeStored($cacheItem, $healthCheck, $healthCheckInterval);
                
            }
        } catch (Exception $e){
            //In the event of an error fall back to running the health check again if possible
            return $healthCheckHandle->runHealthCheck();
        }

        return $healthCheck;
    }

    /**
     * Verifies if a health check has expired (by virtue of it not being in cached or outliving it's TTL)
     *
     * @param HealthCheck|null $healthCheck
     * @param integer $ttl
     * @return bool
     */
    protected function healthCheckExpired(?HealthCheck $healthCheck, int $ttl) : bool{
        return is_null($healthCheck) || $healthCheck->getLastUpdated()->getTimestamp() + $ttl > time();
    }

    /**
     * Queues health checks to purge
     *
     * @param HealthCheckHandlerInterface|null $healthCheckHandle
     * @return void
     */
    protected function queueHealthCheckToBePurged(?HealthCheckHandlerInterface $healthCheckHandle) : void {
        $this->healthChecksToPurge[] = $this->getCacheKeyFromHealthCheckHandle($healthCheckHandle);
    }

    /**
     * Returns health check data from data the cache
     *
     * @param HealthCheckHandlerInterface $healthCheckHandle
     * @return HealthCheck|null
     */
    protected function getHealthCheckFromCache(HealthCheckHandlerInterface $healthCheckHandle) {
        return  $this->cache->getItem(
            $this->getCacheKeyFromHealthCheckHandle($healthCheckHandle)
        );
    }

    /**
     * Queues a health check to be internally stored
     *
     * @param HealthCheck $healthcheck
     * @param integer|null $ttl
     * @return void
     */
    protected function queueHealthCheckToBeStored(CacheItemInterface $cacheItem, HealthCheck $healthCheck, ?int $ttl) : void {
        //Store health check in the event we have a valid ttl and a passing health check
        if (!is_null($ttl) && $ttl > 0 && $healthCheck->passed()) {
            $cacheItem->set($healthCheck);

            // Store against minimum ttl
            $cacheItem->expiresAfter( max(self::CACHE_INTERVAL_MINIMUM_TTL, $ttl ) );
            $this->cache->saveDeferred($cacheItem);
        }
    }

    /**
     * Commits changes to caches made by the cache interface
     *
     * @return void
     */
    public function commit() : void {
        if(is_null($this->cache)){
            //In the event we do not have a cache pool do nothing
            return;
        }

        //Clean out all of the health checks we are looking for
        $this->cache->deleteItems($this->healthChecksToPurge);
        $this->healthChecksToPurge = [];

        //Store the remaining health checks from the cache pool buffer all at once 
        $this->cache->commit();
    }

    /**
     * Generates a namespaced cache key for a health check
     *
     * @param HealthCheckHandlerInterface $healthCheckHandle
     * @return string
     */
    protected function getCacheKeyFromHealthCheckHandle(HealthCheckHandlerInterface $healthCheckHandle) : string {
        return self::CACHE_POOL_PREFIX . $healthCheckHandle->getHealthCheckId();
    }
}
