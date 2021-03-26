<?php

namespace FT\HealthCheckBundle\Factory;

use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckResponseFactory
{
    /**
     * set of required keys to inject into checks
     *
     * @var array
     */
    const REQUIRED_KEYS = [
        'id',
        'name',
        'severity',
        'ok',
        'businessImpact',
        'technicalSummary',
        'panicGuide'
    ];

    /**
     * This is the logging service
     *
     * @var Logger
     */
    public $logger;

    /**
     * A response object matching the v1 health check standard
     *
     * @var array
     */
    protected $response;

    /**
     * @param string $siteInformation
     * @param string $siteName
     * @param string $siteDescription
     * @param Logger $logger
     */
    public function __construct($siteInformation, $siteName, $siteDescription, Logger $logger)
    {
        //Set up health check default parameters
        $this->healthCheckBase = [
            'schemaVersion' => 1,
            'systemCode' => $siteInformation,
            'name' => $siteName,
            'description' => $siteDescription
        ];
        $this->logger = $logger;
    }

    /**
     * Builds a Response object for the request
     *
     * @param HealthCheck[] $checks
     * @return Response
     */
    public function getHealthCheckResponse(array $checks)
    {
        //Filter out all non health check instances
        $checks = array_filter(array_map(function ($check) {
            return $check instanceof HealthCheck ? $check->getHealthCheckArray() : false;
        }, $checks));

        //Validate the remaining instances.
        $check = array_filter($checks, [$this, 'validateCheck']);

        $responseBody = json_encode(
            $this->healthCheckBase + ['checks' => $checks]
        );

        return new Response($responseBody, 200, $this->getResponseHeaders($responseBody));
    }

    /**
     * Validates if a check contains the required data to conform to the FT health check standard
     *
     * @param array $check
     * @return bool
     */
    public function validateCheck($check)
    {
        $missingKeys = array_diff(self::REQUIRED_KEYS, array_keys($check));

        if (!empty($missingKeys)) {
            $this->logger->info('Missing keys '+json_encode($missingKeys)+' from health check '+json_encode($check));
        }

        return empty($missingKeys);
    }

    /**
     * Returns an array of response headers for the health check request
     *
     * @param string $responseBody
     * @return array
     */
    protected function getResponseHeaders($responseBody)
    {
        return [
            'Cache-control' => 'no-store',
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => strlen($responseBody)
        ];
    }
}
