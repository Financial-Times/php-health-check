<?php

namespace FT\HealthCheckBundle\HealthCheck;

use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;

/**
 * This class is used to wrap health check handles that expose themeselves as being be configurable
 */
class ConfigurableHealthCheckHandler implements HealthCheckHandlerInterface
{
    const CONFIG_OVERRIDE_MAPPINGS = [
        'name' => 'withName',
        'severity' => 'withSeverity',
        'business_impact' => 'withBusinessImpact',
        'panic_guide' => 'withPanicGuide',
        'technical_summary' => 'withTechnicalSummary'
    ];

    /**
     * @var HealthCheckHandlerInterface $healthCheckHandle
     */
    protected $healthCheckHandle;

    /**
     * @param HealthCheckHandlerInterface $healthCheckHandle
     * @param string $serviceId
     */
    public function __construct($container ,HealthCheckHandlerInterface $healthCheckHandle, string $serviceId)
    {
        $this->container = $container;
        $this->healthCheckHandle = $healthCheckHandle;
        $this->serviceId = $healthCheckHandle;
    }
    
    /**
     * Decorates the {@see \FT\HealthCheckBundle\HealthCheck\HealthCheck::runHealthCheck()} method by accepting parameters to override health check values 
     * 
     * @return HealthCheck
     */
    public function runHealthCheck() : HealthCheck
    {
        $healthCheck = $this->healthCheckHandle->runHealthCheck();
        
        //Override health check parts that can be overidden with there given props 
        foreach (self::CONFIG_OVERRIDE_MAPPINGS as $parameterName => $methodName) {
            if($this->hasParameter($parameterName)){
                $healthCheck->$methodName($this->getParameter($parameterName));
            }
        }

        return $healthCheck;
    }

    /**
     * Proxies the internal instance of the healthCheckHandle checking for if configuration overrides the interval set in the healthcheck
     *
     * @return integer|null
     */
    public function getHealthCheckInterval(): ?int
    {
        $interval = $this->hasParameter('interval') ?
            $this->getParameter('interval') :
            false;
    
        return (is_int($interval) || is_null($interval)) ?
            $interval :
            $this->healthCheckHandle->getHealthCheckInterval();
    }

    /**
     * Gets if parameter is defined in the context of healthcheck override
     *
     * @param string $paramName
     * @return boolean
     */
    protected function hasParameter(string $paramName):boolean
    {
        return $this->container->hasParameter("${$this->serviceId}.$parmName");
    }

    protected function getParameter(string $paramName)
    {
        return $this->container->getParameter("${$this->serviceId}.$parmName");
    }
}
