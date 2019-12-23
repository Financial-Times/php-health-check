<?php

namespace FT\HealthCheckBundle\Service;

use FT\HealthCheckBundle\HealthCheck\HealthCheck;
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
            $healthCheck = new HealthCheck();
            $healthCheck = $healthCheck
                ->withId($healthCheckHandle->getHealthCheckId())
                ->withName($healthCheckHandle->getHealthCheckId())
                ->withSeverity(3)
                ->withOk(false)
                ->withBusinessImpact("A healthcheck failed to run. It is unknown what effects this would have for users or the editorial team.")
                ->withPanicGuide("Read the output of the check to find where the fatal error was thrown. Note that this healthcheck failing might be a symptom of a larger problem and more serious health check failures should be looked into first.")
                ->withTechnicalSummary("This is a placeholder for the ". get_class($healthCheckHandle) ." heath check that failed to run successfully.")
                ->withCheckOutputException($e);
            return $healthCheck;
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
        return array_map([$this, 'run'], $healthCheckHandles);
    }
}
