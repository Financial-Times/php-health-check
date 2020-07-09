<?php

namespace FT\Tests\HealthCheckBundle\HealthCheck;

use Mockery;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use FT\HealthCheckBundle\HealthCheck\HealthCheckRegistry;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;

/**
 * @coversDefaultClass FT\HealthCheckBundle\HealthCheck\HealthCheckRegistry
 */
class HealthCheckRegistryTest extends TestCase
{
    /**
     * @var HealthCheckRegistry
     */
    protected $healthCheckRegistry;

    /**
     * @var ReflectionProperty
     */
    protected $reflectedRegisteredHealthChecks;

    /**
     * @var ReflectionProperty
     */
    protected $reflectedHealthCheckIdToRunOrderMap;

    protected function setUp() : void
    {
        $this->healthCheckRegistry = new HealthCheckRegistry();

        // Setup reflected registered health checks
        $this->reflectedRegisteredHealthChecks = new ReflectionProperty(HealthCheckRegistry::class, 'registeredHealthChecks');
        $this->reflectedRegisteredHealthChecks->setAccessible(true);

        // Setup reflected registered health check id
        $this->reflectedHealthCheckIdToRunOrderMap = new ReflectionProperty(HealthCheckRegistry::class, 'healthCheckIdToRunOrderMap');
        $this->reflectedHealthCheckIdToRunOrderMap->setAccessible(true);
    }

    /**
     * Tests that a single health check will be set as intended 
     * @covers ::__construct
     * @covers ::registerHealthCheck
     */
    public function test_registerHealthCheck_canSetSingularHealthCheck()
    {
        $healthCheckId = 'MyHealthCheck';

        $healthCheckHandle = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle
            //Handle getting health check id
            ->shouldReceive('getHealthCheckId')
            ->andReturn($healthCheckId)
            ->getMock();
        
        $this->healthCheckRegistry->registerHealthCheck($healthCheckHandle);

        $this->assertEquals(
            $this->reflectedRegisteredHealthChecks->getValue($this->healthCheckRegistry),
            [$healthCheckHandle]
        );

        $this->assertEquals(
            $this->reflectedHealthCheckIdToRunOrderMap->getValue($this->healthCheckRegistry),
            [$healthCheckId => 0]
        );
    }

    /**
     * Tests that multiple health checks will be set as intended 
     * @covers ::__construct
     * @covers ::registerHealthCheck
     */
    public function test_registerHealthCheck_canSetMultipleHealthChecks()
    {
        $healthCheckId1 = 'MyHealthCheck1';
        $healthCheckId2 = 'MyHealthCheck2';
        $healthCheckId3 = 'MyHealthCheck3';

        $healthCheckHandle1 = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle1
            //Handle getting health check id
            ->shouldReceive('getHealthCheckId')
            ->andReturn($healthCheckId1)
            ->getMock();

        $healthCheckHandle2 = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle2
            //Handle getting health check id
            ->shouldReceive('getHealthCheckId')
            ->andReturn($healthCheckId2)
            ->getMock();
        
        $healthCheckHandle3 = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle3
            //Handle getting health check id
            ->shouldReceive('getHealthCheckId')
            ->andReturn($healthCheckId3)
            ->getMock();
        
        $this->healthCheckRegistry->registerHealthCheck($healthCheckHandle1);
        $this->healthCheckRegistry->registerHealthCheck($healthCheckHandle2);
        $this->healthCheckRegistry->registerHealthCheck($healthCheckHandle3);

        $this->assertEquals(
            $this->reflectedRegisteredHealthChecks->getValue($this->healthCheckRegistry),
            [$healthCheckHandle1, $healthCheckHandle2, $healthCheckHandle3]
        );

        $this->assertEquals(
            $this->reflectedHealthCheckIdToRunOrderMap->getValue($this->healthCheckRegistry),
            [
                $healthCheckId1 => 0,
                $healthCheckId2 => 1,
                $healthCheckId3 => 2,
            ]
        );
    }

    /**
     * That health checks with the same ids will raise an error
     * @covers ::__construct
     * @covers ::registerHealthCheck
     */
    public function test_registerHealthCheck_willThrowExceptionOnDuplicateHealthcheck()
    {
        $this->expectExceptionMessageRegExp('/Registered health checks must give valid unique ID/');
        $healthCheckId = 'MyHealthCheck';

        $healthCheckHandle = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle
            //Handle getting health check id
            ->shouldReceive('getHealthCheckId')
            ->andReturn($healthCheckId)
            ->getMock();
        
        $this->healthCheckRegistry->registerHealthCheck($healthCheckHandle);
        $this->healthCheckRegistry->registerHealthCheck($healthCheckHandle);
    }

    /**
     * Tests that if no health checks exist getHealthCheckById should return null
     * @covers ::__construct
     * @covers ::getHealthCheckById
     */
    public function test_getHealthCheckById_willReturnNullIfNoHealthChecksAreRegistered()
    {
        $targetHealthCheckID = 'MyHealthCheckId';
        $this->assertNull($this->healthCheckRegistry->getHealthCheckById($targetHealthCheckID));
    }

    /**
     * Tests that if health checks exist but none with the id given to getHealthCheckById; the method should return null
     * @covers ::__construct
     * @covers ::getHealthCheckById
     */
    public function test_getHealthCheckById_willReturnNullIfNoMatchingHealthChecksAreRegistered()
    {
        $targetHealthCheckID = 'MyHealthCheckId';
        $healthCheckId = 'MyHealthCheckWow';

        $healthCheckHandle = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle
            //Handle getting health check id
            ->shouldReceive('getHealthCheckId')
            ->andReturn($healthCheckId)
            ->getMock();
        
        $this->reflectedHealthCheckIdToRunOrderMap->setValue($this->healthCheckRegistry, [
            $healthCheckHandle
        ]);

        $this->reflectedHealthCheckIdToRunOrderMap->setValue($this->healthCheckRegistry, [
            $healthCheckId => 0
        ]);

        $this->assertNull($this->healthCheckRegistry->getHealthCheckById($targetHealthCheckID));
    }

    /**
     * Tests that health checks can be retrieved by id
     * @covers ::__construct
     * @covers ::getHealthCheckById
     */
    public function test_getHealthCheckById_willReturnHelathcheckIfMatchingHealthChecksAreRegistered()
    {
        $healthCheckId1 = 'MyHealthCheck1';
        $healthCheckId2 = 'MyHealthCheck2';
        $healthCheckId3 = 'MyHealthCheck3';

        $healthCheckHandle1 = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle2 = Mockery::mock(HealthCheckHandlerInterface::class);
        $healthCheckHandle3 = Mockery::mock(HealthCheckHandlerInterface::class);

        $this->reflectedRegisteredHealthChecks->setValue($this->healthCheckRegistry, [$healthCheckHandle1, $healthCheckHandle2, $healthCheckHandle3]);
        
        $this->reflectedHealthCheckIdToRunOrderMap->setValue($this->healthCheckRegistry, [
            $healthCheckId1 => 0,
            $healthCheckId2 => 1,
            $healthCheckId3 => 2,
        ]);

        $this->assertEquals(
            $this->healthCheckRegistry->getHealthCheckById($healthCheckId2),
            $healthCheckHandle2
        );

        $this->assertEquals(
            $this->healthCheckRegistry->getHealthCheckById($healthCheckId1),
            $healthCheckHandle1
        );
        
        $this->assertEquals(
            $this->healthCheckRegistry->getHealthCheckById($healthCheckId3),
            $healthCheckHandle3
        );
    }

    protected function runHealthCheck() : void
    {
        Mockery::close();
    }
}
