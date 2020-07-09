<?php
declare(strict_types=1);

namespace FT\HealthCheckBundle\Service;

use Exception;
use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\Factory\HealthCheckFactory;
use FT\HealthCheckBundle\Service\CachedHealthCheckService;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;
use Throwable;

/**
 * Service used to run given health checks
 */
class HealthCheckExecutorService
{

    /** @var CachedHealthCheckService */
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
    public function run(HealthCheckHandlerInterface $healthCheckHandle): HealthCheck
    {
        try {
            return $this->cachedHealthCheckService->runHealthCheckHandle($healthCheckHandle);
        } catch (Throwable $e) {
           return HealthCheckFactory::buildFailingHealthCheck($healthCheckHandle, $e);
        }
    }

    /**
     * Will run all the given health check handles and return an array of health checks in the order they were given
     *
     * @param HealthCheckHandlerInterface[] $healthCheckHandles
     * @return HealthCheck[]
     */
    public function runAll(array $healthCheckHandles): array
    {
        $result = array_map([$this, 'run'], $healthCheckHandles);

        //Try to save any health checks but otherwise fail gracefully
        try {
            $this->cachedHealthCheckService->commit();
        } catch(Exception $e){}
        return $result;
    }
}
