<?php
declare(strict_types=1);

namespace FT\HealthCheckBundle\HealthCheck;

use DomainException;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;

/**
 * Used to store the various health checks used across the application
 */
class HealthCheckRegistry {
    /**
     * Contains an array of health checks ordered by priority from highest to lowest 
     *
     * @var HealthCheckHandlerInterface[]
     */
    protected $registeredHealthChecks;

    /**
     * Contains a mapping between health check Id's and their given run order. 
     * Used as a lookup table when retrieving health checks by id .
     * Given in the form ['Health Check Id' => {int}]
     *
     * @var int[]
     */
    protected $healthCheckIdToRunOrderMap;

    public function __construct() {
        $this->registeredHealthChecks = [];
        $this->healthCheckIdToRunOrderMap = [];
    }

    /**
     * Registers a health check with this service 
     *
     * @throws DomainException (If the health check given has a non unique id)
     * @param HealthCheckHandlerInterface $healthCheck
     * @return void
     */
    public function registerHealthCheck(HealthCheckHandlerInterface $healthCheck): void{
        $healthCheckId = $healthCheck->getHealthCheckId();

        if(array_key_exists($healthCheckId, $this->healthCheckIdToRunOrderMap)){
            // Add protection against unique key violation
            throw new DomainException("Registered health checks must give valid unique ID. Id '$healthCheckId' given by instance of ".
                 get_class($healthCheck) . ' already in use by instance of ' . get_class($this->getHealthCheckById($healthCheckId)) . '.');
        }

        // We keep the health checks in order (not indexing by key) given the are registered given in a specific order
        $this->registeredHealthChecks[] = $healthCheck;

        // Index the health check using an in memory look up table so we can quickly query by healthcheck Id 
        $this->healthCheckIdToRunOrderMap[$healthCheck->getHealthCheckId()] = count($this->registeredHealthChecks) - 1;
    }

    /**
     * Gets all the registered health checks in the order they need to be run 
     *
     * @return HealthCheckHandlerInterface[]
     */
    public function getAllHealthChecks(): array 
    {
        return $this->registeredHealthChecks;
    }

    /**
     * Gets health check from registry by id
     *
     * @param string $healthCheckId
     * @return HealthCheckHandlerInterface|null
     */
    public function getHealthCheckById(string $healthCheckId): ?HealthCheckHandlerInterface 
    {
        if(!array_key_exists($healthCheckId, $this->healthCheckIdToRunOrderMap)){
            return null;
        }

        return $this->registeredHealthChecks[
            $this->healthCheckIdToRunOrderMap[$healthCheckId]
        ];  
    }
}
