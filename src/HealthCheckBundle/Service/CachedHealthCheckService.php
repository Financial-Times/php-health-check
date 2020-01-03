<?php

namespace FT\HealthCheckBundle\Service;

use Exception;
use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class CachedHealthCheckService
{
    const CACHE_POOL_SERVICE_PARAMETER_ID = "health_check.cache_pool";

    /**
     * Ez publish cache service.
     *
     * @var Pool
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
     * @param Logger $logger
     * @param Pool $cache
     */
    public function __construct(Logger $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
    }

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
            $cacheKey = $healthCheckHandle->getHealthCheckId();

            $cacheItem = $this->cache->getItem($cacheKey);

            //If this healthCheck is not cached
            if (!$cacheItem->isHit()) {
                //Run the health check
                $healthCheck = $healthCheckHandle->runHealthCheck();

                //In the event we only run it every so often
                if ($healthCheck->passed()) {
                    $cacheItem->set($healthCheck);
                    $cacheItem->expiresAfter($healthCheckHandle->getHealthCheckInterval());
                    $cacheItem->save();
                }
            } else {
                $healthCheck = $cacheItem->get();
            }
        } catch (Exception $e){
            //In the event of an error fall back to running the health check again if possible
            return $healthCheckHandle->runHealthCheck();
        }

        return $healthCheck;
    }
}
