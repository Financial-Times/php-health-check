<?php

namespace FT\HealthCheckBundle\HealthCheck;

/**
 * Defines a standard format for the health check interface
 */
interface HealthCheckHandlerInterface
{
    /**
     * Called to execute the health check. Note that it is expected for all errors that could be thrown during the healthcheck are handled
     *
     * @return HealthCheck
     */
    public function runHealthCheck(): HealthCheck;

    /**
     * Signifies a unique identifier for the health check
     *
     * @return string
     */
    public function getHealthCheckId(): string;

    /**
     * This method dictates how often the health check should run. Return null if check should always be run
     *
     * @return integer
     */
    public function getHealthCheckInterval(): ?int;
}
