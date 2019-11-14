<?php

namespace FT\HealthCheckBundle\HealthCheck;

use Symfony\Component\DependencyInjection\Container;

/**
 * This class is used to wrap health check handles that expose themeselves as being be configurable.
 */
class ConfigurableHealthCheckHandler implements HealthCheckHandlerInterface
{
    const CONFIG_OVERRIDE_MAPPINGS = [
        'name' => 'withName',
        'severity' => 'withSeverity',
        'business_impact' => 'withBusinessImpact',
        'panic_guide' => 'withPanicGuide',
        'technical_summary' => 'withTechnicalSummary',
    ];

    /**
     * @var HealthCheckHandlerInterface
     */
    protected $healthCheckHandle;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     * @param HealthCheckHandlerInterface $healthCheckHandle
     * @param string $serviceId
     */
    public function __construct(Container $container, HealthCheckHandlerInterface $healthCheckHandle, string $serviceId)
    {
        $this->container = $container;
        $this->healthCheckHandle = $healthCheckHandle;
        $this->serviceId =  $serviceId;
    }

    /**
     * Decorates the {@see \FT\HealthCheckBundle\HealthCheck\HealthCheck::runHealthCheck()} method by accepting parameters to override health check values.
     *
     * @return HealthCheck
     */
    public function runHealthCheck(): HealthCheck
    {
        $healthCheck = $this->healthCheckHandle->runHealthCheck();

        //Override health check parts that can be overidden with there given props
        foreach (self::CONFIG_OVERRIDE_MAPPINGS as $parameterName => $methodName) {
            if ($this->hasParameter($parameterName)) {
                $healthCheck->{$methodName}($this->getParameter($parameterName));
            }
        }

        return $healthCheck;
    }

    /**
     * Proxies the internal instance of the healthCheckHandle checking for if configuration overrides the interval set in the healthcheck.
     *
     * @return int|null
     */
    public function getHealthCheckInterval(): ?int
    {
        $interval = $this->hasParameter('interval') ?
            $this->getParameter('interval') :
            false;

        return (\is_int($interval) || null === $interval) ?
            $interval :
            $this->healthCheckHandle->getHealthCheckInterval();
    }

    /**
     * Retrieves the health check from config if possible, returning the id from the internal healthCheckHandle instance if not.
     *
     * @return string
     */
    public function getHealthCheckId(): string
    {
        return $this->hasParameter('id') ?
            $this->getParameter('interval') :
            $this->healthCheckHandle->getHealthCheckId();
    }

    /**
     * Gets if parameter is defined in the context of healthcheck override.
     *
     * @param string $paramName
     * @return bool
     */
    protected function hasParameter(string $paramName): bool
    {
        return $this->container->hasParameter($this->serviceId. '.' . $paramName);
    }

    /**
     * @param string $paramName
     * @return string
     */
    protected function getParameter(string $paramName): string
    {
        return $this->container->getParameter($this->serviceId . '.' . $paramName);
    }
}
