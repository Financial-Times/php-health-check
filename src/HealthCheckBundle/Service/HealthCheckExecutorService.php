<?php

namespace FT\HealthCheckBundle\Service;

use Exception;
use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\Factory\HealthCheckFactory;
use FT\HealthCheckBundle\Service\CachedHealthCheckService;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;

/**
 * Service used to run given health checks
 */
class HealthCheckExecutorService
{

    /**
     * @var CachedHealthCheckService
     */
    protected $cachedHealthCheckService;

    /**
     * @param CachedHealthCheckService $cachedHealthCheckService
     */
    public function __construct(CachedHealthCheckService $cachedHealthCheckService)
    {
        $this->cachedHealthCheckService = $cachedHealthCheckService;
    }

    /**
     * Will execute a single health check handle and return it's respective health check
     *
     * @param HealthCheckHandlerInterface $healthCheckHandle
     * @return HealthCheck
     */
    public function run(HealthCheckHandlerInterface $healthCheckHandle) : HealthCheck
    {
        try {
            return $this->cachedHealthCheckService->runHealthCheckHandle($healthCheckHandle);
        } catch (Exception $e) {
           return HealthCheckFactory::buildHealthCheckFromFailingHealthCheckHandle($healthCheckHandle);
        }
    }

    /**
     * Will run all the given health check handles and return an array of health checks in the order they were given
     *
     * @param HealthCheckHandlerInterface[] $healthCheckHandles
     * @return HealthCheck[]
     */
    public function runAll(array $healthCheckHandles) : array
    {
        $result = array_map([$this, 'run'], $healthCheckHandles);
        $this->cachedHealthCheckService->commit();
        return $result;
    }
}
