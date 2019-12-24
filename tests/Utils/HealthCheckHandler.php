<?php

namespace FT\Tests\Utils;

use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;

/**
 * Acts as a test proxy for interfacing with test things that use the Health check handler interface
 */
class HealthCheckHandler implements HealthCheckHandlerInterface
{
    /**
     * Health check to to return when executing health check
     *
     * @var HealthCheck
     */
    protected $healthCheck;

    /**
     * @var string
     */
    protected $healthCheckId;

    /**
     * @var int
     */
    protected $interval;

    public function __construct(
        ?HealthCheck $healthCheck = null,
        ?string $healthCheckId = null,
        ?int $interval = null
    ) {
        $this->healthCheck = $healthCheck ?? new HealthCheck();
        $this->healthCheckId = $healthCheckId ?? 'TestId';
        $this->interval = $interval;
    }

    public function runHealthCheck() : HealthCheck
    {
        return $this->healthCheck;
    }

    public function getHealthCheckId() : string
    {
        return $this->healthCheckId;
    }

    public function getHealthCheckInterval() : ?int
    {
        return $this->interval;
    }
}
