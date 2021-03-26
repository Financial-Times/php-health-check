<?php

namespace FT\HealthCheckBundle\Controller;

use Exception;
use FT\HealthCheckBundle\HealthCheck\HealthCheckRegistry;
use FT\HealthCheckBundle\Factory\HealthCheckResponseFactory;
use FT\HealthCheckBundle\Service\HealthCheckExecutorService;

class HealthCheckController
{
    /**
     * Stores all the handles that should be executed when the {@see self::healthCheckAction} method is called
     *
     * @var HealthCheckRegistry
     */
    protected $healthCheckRegistry;

    /**
     * @var HealthCheckResponseFactory
     */
    protected $healthCheckResponseFactory;

    /**
     * The health check executor service
     *
     * @var HealthCheckExecutorService
     */
    protected $healthCheckExecutorService;

    public function __construct(
        HealthCheckResponseFactory $healthCheckResponseFactory,
        HealthCheckRegistry $healthCheckRegistry,
        HealthCheckExecutorService $healthCheckExecutorService
    ) {
        $this->healthCheckResponseFactory = $healthCheckResponseFactory;
        $this->healthCheckRegistry = $healthCheckRegistry;
        $this->healthCheckExecutorService = $healthCheckExecutorService;
    }

    /**
     * Executes all health checks as part of the __health endpoint
     */
    public function healthCheckAction()
    {
        return $this->healthCheckResponseFactory->getHealthCheckResponse(
            $this->healthCheckExecutorService->runAll(
                $this->healthCheckRegistry->getAllHealthChecks()
            )
        );
    }
}
