<?php

namespace FT\HealthCheckBundle\Factory;

/**
 * Holds a collection of methods to aid in building health check objects 
 */
class HealthCheckFactory{
    static public function buildHealthCheckFromFailingHealthCheckHandle(HealthCheckHandlerInterface $healthCheckHandle) : HealthCheck {
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