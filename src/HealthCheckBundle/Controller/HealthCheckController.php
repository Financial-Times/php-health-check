<?php

namespace FT\HealthCheckBundle\Controller;

use FT\HealthCheckBundle\Factory\HealthCheckResponseFactory;
use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;
use FT\HealthCheckBundle\Service\CachedHealthCheckService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Exception;

class HealthCheckController extends Controller
{
    /**
     * Stores all the handles that should be executed when the {@see self::healthCheckAction} method is called
     *
     * @var HealthCheckHandlerInterface[]
     */
    protected $healthCheckHandles;

    /**
     * @var HealthCheckResponseFactory
     */
    protected $healthCheckResponseFactory;

    /**
     * @var CachedHealthCheckService
     */
    protected $cachedHealthCheckService;

    public function __construct(
        HealthCheckResponseFactory $healthCheckResponseFactory,
        CachedHealthCheckService $cachedHealthCheckService
    ) {
        $this->healthCheckResponseFactory = $healthCheckResponseFactory;
        $this->cachedHealthCheckService = $cachedHealthCheckService;
        $this->healthCheckHandles = [];
    }

    /**
     * Used to register a health check handle that should be executed when {@see self::healthCheckAction} is called.
     *
     * @param HealthCheckHandlerInterface $healthCheck
     * @return void
     */
    public function addHealthCheck(HealthCheckHandlerInterface $healthCheck)
    {
        $this->healthCheckHandles[] = $healthCheck;
    }

    /**
     * Executes all health checks as part of the __health endpoint
     */
    public function healthCheckAction()
    {
        $healthChecks = array_map(function ($healthCheckHandle) {
            try{
                $healthCheck = $this->cachedHealthCheckService->runHealthCheckHandle($healthCheckHandle);
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
            }
            return $healthCheck;
        }, $this->healthCheckHandles);
        return $this->healthCheckResponseFactory->getHealthCheckResponse($healthChecks);
    }
}
