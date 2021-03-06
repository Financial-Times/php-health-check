<?php

namespace FT\Tests\HealthCheckBundle\Controller;

use Mockery;
use HealthCheckBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\HealthCheck\HealthCheckRegistry;
use FT\HealthCheckBundle\Controller\HealthCheckController;
use FT\HealthCheckBundle\Factory\HealthCheckResponseFactory;
use FT\HealthCheckBundle\Service\HealthCheckExecutorService;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;

/**
 * @coversDefaultClass \FT\HealthCheckBundle\Controller\HealthCheckController
 */
class HealthCheckControllerTest extends TestCase {
    /** @var Mockery */
    protected $healthCheckResponseFactory;

    /** @var Mockery */
    protected $healthCheckRegistry;

    /** @var Mockery */
    protected $healthCheckExecutor;
    
    function setUp(): void{
        $this->healthCheckResponseFactory = Mockery::mock(HealthCheckResponseFactory::class);
        $this->healthCheckRegistry = Mockery::mock(HealthCheckRegistry::class);
        $this->healthCheckExecutor =  Mockery::mock(HealthCheckExecutorService::class);
    }
    
    /**
     * @covers ::__construct
     * @covers ::healthCheckAction
     */
    public function test_healthCheckAction_executesAllHealthChecks()
    {
        $expectedResponse = new Response('Cool health checks!');

        $healthChecksGiven = [
            new HealthCheck(),
            new HealthCheck()
        ];

        $healthCheckHandlersToExecute = [
            Mockery::mock(HealthCheckHandlerInterface::class),
            Mockery::mock(HealthCheckHandlerInterface::class)
        ];

        $this->healthCheckRegistry
            //Call get all health checks to retrieve what we are looking out for
            ->shouldReceive('getAllHealthChecks')
            ->andReturn($healthCheckHandlersToExecute)
            ->getMock();
        
        $this->healthCheckExecutor
            //Execute all healthchecks
            ->shouldReceive('runAll')
            ->with($healthCheckHandlersToExecute)
            ->andReturn($healthChecksGiven)
            ->getMock();
        
        $this->healthCheckResponseFactory
            ->shouldReceive('getHealthCheckResponse')
            ->with($healthChecksGiven)
            ->andReturn($expectedResponse)
            ->getMock();

        $healthCheckController = new HealthCheckController($this->healthCheckResponseFactory, $this->healthCheckRegistry, $this->healthCheckExecutor);
    
        $this->assertEquals($expectedResponse, $healthCheckController->healthCheckAction());
    }

    public function tearDown() : void{
        Mockery::close();
    }
}