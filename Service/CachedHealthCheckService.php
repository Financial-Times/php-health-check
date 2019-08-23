<?php

namespace FT\HealthCheckBundle\Service;

use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;
use \Monolog\Logger;
use \Stash\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use RedisException;

class CachedHealthCheckService
{

    /**
     * Ez publish cache service.
     *
     * @var Pool
     */
    protected $cache;

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

        try{
            $this->cache = $container->get('ezpublish.cache_pool');
        } catch (Exception $e){
            $this->cache = null;
        }
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

        $cacheKey = $healthCheckHandle->getHealthCheckId();

        $cacheItem = $this->cache->getItem($cacheKey);

        //If this healthCheck is not cached
        if ($cacheItem->isMiss()) {
            //Run the health check
            $healthCheck = $healthCheckHandle->runHealthCheck();

            //In the event we only run it every so often
            if ($healthCheck->passed()) {
                $cacheItem->set($healthCheck);
                $cacheItem->setTTL($healthCheckHandle->getHealthCheckInterval());
                $cacheItem->save();
            }
        } else {
            $healthCheck = $cacheItem->get();
        }

        return $healthCheck;
    }
}
