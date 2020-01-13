<?php

namespace FT\Tests\HealthCheckBundle\Service;

use Mockery;
use Exception;
use PHPUnit\Framework\TestCase;
use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use FT\HealthCheckBundle\Service\CachedHealthCheckService;
use FT\HealthCheckBundle\Service\HealthCheckExecutorService;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;

/**
 * @coversDefaultClass \FT\HealthCheckBundle\Service\HealthCheckExecutorService
 */
class HealthCheckExecutorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $this->cachedHealthCheckService = Mockery::mock(CachedHealthCheckService::class);
    }

    /*
     * Tests that exceptions given by health checks can be caught and handled when executing health checks
     * @covers ::__construct
     * @covers ::run
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     */
    public function test_run_failingHealthCheck()
    {
        $failingHealthCheck = new HealthCheck();

        $healthCheckHandle = Mockery::mock(HealthCheckHandlerInterface::class);

        $e = new Exception('Oh no something bad happened that was uncaught in the healthcheck!');

        $healthCheckFactory = Mockery::mock('overload:FT\HealthCheckBundle\Factory\HealthCheckFactory');
        $healthCheckFactory
            //Handle internal call to build a healthcheck with the correct state
            ->shouldReceive('buildHealthCheckFromFailingHealthCheckHandle')
            ->withArgs([$healthCheckHandle, $e])
            ->andReturn($failingHealthCheck)
            ->getMock();
           
        $this->cachedHealthCheckService
            ->shouldReceive('runHealthCheckHandle')
            ->with($healthCheckHandle)
            ->andThrow($e)
            ->getMock();
        
        $healthCheckExecutor = new HealthCheckExecutorService($this->cachedHealthCheckService);

        $result = $healthCheckExecutor->run($healthCheckHandle);

        $this->assertEquals($result, $failingHealthCheck);
    }

    /**
     * Tests that executor can execute health checks
     * @covers ::custom
     * @covers ::run
     */
    public function test_run_canExecuteHealthCheck()
    {
        $healthCheck = new HealthCheck();

        $healthCheckHandle = Mockery::mock(HealthCheckHandlerInterface::class);

        $this->cachedHealthCheckService
            ->shouldReceive('runHealthCheckHandle')
            ->with($healthCheckHandle)
            ->andReturn($healthCheck)
            ->getMock();
        
        $healthCheckExecutor = new HealthCheckExecutorService($this->cachedHealthCheckService);

        $result = $healthCheckExecutor->run($healthCheckHandle);

        $this->assertEquals($result, $healthCheck);
    }

    /**
     * Tests that run sequentially receives health checks to execute when calling runAll
     * @covers ::runAll
     */
    public function test_runAll_canExecuteHealthCheck()
    {
        $healthCheck1 = new HealthCheck();
        $healthCheck2 = new HealthCheck();
        $healthCheck3 = new HealthCheck();

        $healthCheckHandle1 = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle2 = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle3 = Mockery::mock(HealthCheckHandlerInterface::class);

        $this->cachedHealthCheckService
            //Handle committing deferred health checks
            ->shouldReceive('commit')
            ->getMock();

        $healthCheckExecutorService = Mockery::mock(HealthCheckExecutorService::class, [$this->cachedHealthCheckService])->makePartial();

        $healthCheckExecutorService
            // Handle 1st call
            ->shouldReceive('run')
            ->with($healthCheckHandle1)
            ->andReturn($healthCheck1)
            // Handle 2nd call
            ->shouldReceive('run')
            ->with($healthCheckHandle2)
            ->andReturn($healthCheck2)
            // Handle 3rd call
            ->shouldReceive('run')
            ->with($healthCheckHandle3)
            ->andReturn($healthCheck3)
            ->getMock();
        
        $result = $healthCheckExecutorService->runAll([
            $healthCheckHandle1,
            $healthCheckHandle2,
            $healthCheckHandle3
        ]);

        $this->assertEquals($result, [
            $healthCheck1,
            $healthCheck2,
            $healthCheck3
        ]);
    }

    /**
     * @covers ::run
     */
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
