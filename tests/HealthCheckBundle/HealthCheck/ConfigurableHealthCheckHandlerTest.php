<?php

namespace FT\Tests\HealthCheckBundle\Controller;

use Mockery;
use PHPUnit\Framework\TestCase;
use FT\Tests\Utils\HealthCheckHandler;
use FT\HealthCheckBundle\HealthCheck\HealthCheck;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FT\HealthCheckBundle\HealthCheck\HealthCheckHandlerInterface;
use FT\HealthCheckBundle\HealthCheck\ConfigurableHealthCheckHandler;

/**
 * @coversDefaultClass \FT\HealthCheckBundle\HealthCheck\ConfigurableHealthCheckHandler
 */
class ConfigurableHealthCheckHandlerTest extends TestCase
{

    /**
     * @var Mockery
     */
    protected $healthCheck;

    /**
     * @var Mockery
     */
    protected $healthCheckHandle;

    /**
     * @var Mockery
     */
    protected $container;

    protected function setUp() : void
    {
        $this->healthCheck = Mockery::mock(HealthCheck::class);
        $this->healthCheckHandle = Mockery::mock(HealthCheckHandlerInterface::class);
        $this->container = Mockery::mock(Container::class);
    }
    
    /**
     * This test covers nothing being configured on the healthcheck (should just forward the return value)
     * @covers ::__construct
     * @covers ::runHealthCheck
     * @covers ::hasParameter
     */
    public function test_runHealthCheck_canExecuteHealthCheckWithoutOverrides()
    {
        $this->healthCheckHandle
            //Handle fake health check execution
            ->shouldReceive('runHealthCheck')
            ->andReturn($this->healthCheck)
            ->getMock();

        $this->container
            // Handle all calls to has parameter as if we have none of these parameters
            ->shouldReceive('hasParameter')
            ->with(Mockery::on(function ($arg) {
                return is_string($arg);
            }))
            ->andReturn(false)
            ->getMock();
        
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');

        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();

        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
     * This test that the name of a configurable health check can be overridden
     * @covers ::__construct
     * @covers ::runHealthCheck
     * @covers ::hasParameter
     * @covers ::getParameter
     */
    public function test_runHealthCheck_canExecuteHealthCheckWithNameOverride()
    {
        $parameterPath = 'health_check.thing.name';
        $parameterValue = 'Some Health Check Name';

        $this->healthCheck
            //Mock the call to the expected parameter we are going to be overriding on the healthcheck
            ->shouldReceive('withName')
            ->with($parameterValue)
            ->andReturn($this->healthCheck)
            ->getMock();

        $this->healthCheckHandle
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
 
        $this->container
             // Handle all calls to hasParameter as if don't have anything but the parameter we are testing
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($parameterPath) {
                 return $arg !== $parameterPath;
             }))
             ->andReturn(false)
             // Handle the call to hasParameter where we actually expect to get a value
             ->shouldReceive('hasParameter')
             ->with($parameterPath)
             ->andReturn(true)
             // Handle the call where we actually get the parameter
             ->shouldReceive('getParameter')
             ->with($parameterPath)
             ->andReturn($parameterValue)
             ->getMock();
         
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');
 
        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();
 
        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
    * This test that the severity of a configurable health check can be overridden
    * @covers ::__construct
    * @covers ::runHealthCheck
    * @covers ::hasParameter
    * @covers ::getParameter
    */
    public function test_runHealthCheck_canExecuteHealthCheckWithSeverityOverride()
    {
        $parameterPath = 'health_check.thing.severity';
        $parameterValue = 1;

        $this->healthCheck
            //Mock the call to the expected parameter we are going to be overriding on the healthcheck
            ->shouldReceive('withSeverity')
            ->with($parameterValue)
            ->andReturn($this->healthCheck)
            ->getMock();

        $this->healthCheckHandle
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
 
        $this->container
             // Handle all calls to hasParameter as if don't have anything but the parameter we are testing
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($parameterPath) {
                 return $arg !== $parameterPath;
             }))
             ->andReturn(false)
             // Handle the call to hasParameter where we actually expect to get a value
             ->shouldReceive('hasParameter')
             ->with($parameterPath)
             ->andReturn(true)
             // Handle the call where we actually get the parameter
             ->shouldReceive('getParameter')
             ->with($parameterPath)
             ->andReturn($parameterValue)
             ->getMock();
         
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');
 
        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();
 
        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
     * This test that the business impact  of a configurable health check can be overridden
     * @covers ::__construct
     * @covers ::runHealthCheck
     * @covers ::hasParameter
     * @covers ::getParameter
     */
    public function test_runHealthCheck_canExecuteHealthCheckWithBusinessImpactOverride()
    {
        $parameterPath = 'health_check.thing.business_impact';
        $parameterValue = 'Things has gone wrong :(. Users cannot do stuff and things';

        $this->healthCheck
            //Mock the call to the expected parameter we are going to b
            ->shouldReceive('withBusinessImpact')
            ->with($parameterValue)
            ->andReturn($this->healthCheck)
            ->getMock();

        $this->healthCheckHandle
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
 
        $this->container
             // Handle all calls to hasParameter as if don't have anything but the parameter we are testing
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($parameterPath) {
                 return $arg !== $parameterPath;
             }))
             ->andReturn(false)
             // Handle the call to hasParameter where we actually expect to get a value
             ->shouldReceive('hasParameter')
             ->with($parameterPath)
             ->andReturn(true)
             // Handle the call where we actually get the parameter
             ->shouldReceive('getParameter')
             ->with($parameterPath)
             ->andReturn($parameterValue)
             ->getMock();
         
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');
 
        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();
 
        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
    * This test that the panic guide of a configurable health check can be overridden
    * @covers ::__construct
    * @covers ::runHealthCheck
    * @covers ::hasParameter
    * @covers ::getParameter
    */
    public function test_runHealthCheck_canExecuteHealthCheckWithPanicGuideOverride()
    {
        $parameterPath = 'health_check.thing.panic_guide';
        $parameterValue = 'Despair, there is nothing that ca be done now. Please run in circles screaming in terror!';

        $this->healthCheck
            //Mock the call to the expected parameter we are going to b
            ->shouldReceive('withPanicGuide')
            ->with($parameterValue)
            ->andReturn($this->healthCheck)
            ->getMock();

        $this->healthCheckHandle
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
 
        $this->container
             // Handle all calls to hasParameter as if don't have anything but the parameter we are testing
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($parameterPath) {
                 return $arg !== $parameterPath;
             }))
             ->andReturn(false)
             // Handle the call to hasParameter where we actually expect to get a value
             ->shouldReceive('hasParameter')
             ->with($parameterPath)
             ->andReturn(true)
             // Handle the call where we actually get the parameter
             ->shouldReceive('getParameter')
             ->with($parameterPath)
             ->andReturn($parameterValue)
             ->getMock();
         
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');
 
        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();
 
        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
    * This test that the panic guide of a configurable health check can be overridden
    * @covers ::__construct
    * @covers ::runHealthCheck
    * @covers ::hasParameter
    * @covers ::getParameter
    */
    public function test_runHealthCheck_canExecuteHealthCheckWithTechnicalSummaryOverride()
    {
        $parameterPath = 'health_check.thing.technical_summary';
        $parameterValue = 'Quantum nano-technology CPU.';

        $this->healthCheck
            //Mock the call to the expected parameter we are going to b
            ->shouldReceive('withTechnicalSummary')
            ->with($parameterValue)
            ->andReturn($this->healthCheck)
            ->getMock();

        $this->healthCheckHandle
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
 
        $this->container
             // Handle all calls to hasParameter as if don't have anything but the parameter we are testing
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($parameterPath) {
                 return $arg !== $parameterPath;
             }))
             ->andReturn(false)
             // Handle the call to hasParameter where we actually expect to get a value
             ->shouldReceive('hasParameter')
             ->with($parameterPath)
             ->andReturn(true)
             // Handle the call where we actually get the parameter
             ->shouldReceive('getParameter')
             ->with($parameterPath)
             ->andReturn($parameterValue)
             ->getMock();
         
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');
 
        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();
 
        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
    * This test that the panic guide, name and severity of a configurable health check can be overridden
    * @covers ::__construct
    * @covers ::runHealthCheck
    * @covers ::hasParameter
    * @covers ::getParameter
    */
    public function test_runHealthCheck_canExecuteHealthCheckWithNameAndPanicGuideAndSeverityOverride()
    {
        $nameParameterPath = 'health_check.thing.name';
        $nameParameterValue = 'My cool health check!';

        $severityParameterPath = 'health_check.interval.severity';
        $severityParameterValue = 3;

        $panicGuideParameterPath = 'health_check.interval.panic_guide';
        $panicGuideParameterValue = 'RUN!!!!';

        $this->healthCheck
            //Handle with name
            ->shouldReceive('withName')
            ->with($nameParameterValue)
            ->andReturn($this->healthCheck)
            //Handle with name
            ->shouldReceive('withSeverity')
            ->with($severityParameterValue)
            ->andReturn($this->healthCheck)
            //Handle with name
            ->shouldReceive('withPanicGuide')
            ->with($panicGuideParameterValue)
            ->andReturn($this->healthCheck)
            ->getMock();

        $this->healthCheckHandle
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
 
        $this->container
             // Handle all calls to hasParameter as if don't have anything but the parameter we are testing
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($nameParameterPath, $severityParameterPath, $panicGuideParameterPath) {
                 return !in_array($arg, [$nameParameterPath, $severityParameterPath, $panicGuideParameterPath], true);
             }))
             ->andReturn(false)
             // Handle the call to hasParameter where we actually expect to get a value
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($nameParameterPath, $severityParameterPath, $panicGuideParameterPath) {
                 return in_array($arg, [$nameParameterPath, $severityParameterPath, $panicGuideParameterPath], true);
             }))
             ->andReturn(true)
             // Handle the call where we actually get the parameter for name
             ->shouldReceive('getParameter')
             ->with($nameParameterPath)
             ->andReturn($nameParameterValue)
             // Handle the call where we actually get the parameter for name
             ->shouldReceive('getParameter')
             ->with($severityParameterPath)
             ->andReturn($severityParameterValue)
             // Handle the call where we actually get the parameter for name
             ->shouldReceive('getParameter')
             ->with($panicGuideParameterPath)
             ->andReturn($panicGuideParameterValue)
             ->getMock();
         
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');
 
        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();
 
        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
     * Tests that health check ids can be overridden
     * @covers ::__construct
     * @covers ::getHealthCheckId
     * @covers ::hasParameter
     * @covers ::getParameter
     */
    public function test_getHealthCheckId_canOverrideHealthCheckId()
    {
        $idParameterPath = 'health_check.thing.id';
        $idParameterValue = 'MyThingHealthCheck';

        $this->healthCheckHandle
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
        
        $this->container
             // Handle all calls to has parameter as if we have none of these parameters
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($idParameterPath) {
                 return $arg !== $idParameterPath;
             }))
             ->andReturn(false)
             //Handle call where we do get the parameter we are looking for
             ->shouldReceive('hasParameter')
             ->with($idParameterPath)
             ->andReturn(true)
             //Handle where we get the parameter
             ->shouldReceive('getParameter')
             ->with($idParameterPath)
             ->andReturn($idParameterValue)
             ->getMock();
        
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');

        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();

        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
     * Tests that health check ids are left alone when not overidden
     * @covers ::__construct
     * @covers ::getHealthCheckId
     * @covers ::hasParameter
     * @covers ::getParameter
     */
    public function test_getHealthCheckId_canUseDefaultHealthCheckId()
    {
        $idParameterValue = 'MyThingHealthCheck';

        $this->healthCheckHandle
             // Handle getting default health check ID
             ->shouldReceive('getHealthCheckId')
             ->andReturn($idParameterValue)
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
        
        $this->container
             // Handle all calls to has parameter as if we have none of these parameters
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) {
                 return is_string($arg);
             }))
             ->andReturn(false)
             ->getMock();
        
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');

        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();

        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
     * Tests that health check intervals can be overridden
     * @covers ::__construct
     * @covers ::getHealthCheckId
     * @covers ::hasParameter
     * @covers ::getParameter
     */
    public function test_getHealthCheckId_canOverrideHealthCheckInterval()
    {
        $intervalParameterPath = 'health_check.thing.interval';
        $intervalParameterValue = 100;

        $this->healthCheckHandle
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
        
        $this->container
             // Handle all calls to has parameter as if we have none of these parameters
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) use ($intervalParameterPath) {
                 return $arg !== $intervalParameterPath;
             }))
             ->andReturn(false)
             //Handle call where we do get the parameter we are looking for
             ->shouldReceive('hasParameter')
             ->with($intervalParameterPath)
             ->andReturn(true)
             //Handle where we get the parameter
             ->shouldReceive('getParameter')
             ->with($intervalParameterPath)
             ->andReturn($intervalParameterValue)
             ->getMock();
        
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');

        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();

        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    /**
     * Tests that health check intervals are left alone when not overidden
     * @covers ::__construct
     * @covers ::getHealthCheckId
     * @covers ::hasParameter
     * @covers ::getParameter
     */
    public function test_getHealthCheckId_canUseDefaultHealthCheckInterval()
    {
        $intervalParameterValue = 100;

        $this->healthCheckHandle
             // Handle getting default health check ID
             ->shouldReceive('getHealthCheckInterval')
             ->andReturn($intervalParameterValue)
             //Handle fake health check execution
             ->shouldReceive('runHealthCheck')
             ->andReturn($this->healthCheck)
             ->getMock();
        
        $this->container
             // Handle all calls to has parameter as if we have none of these parameters
             ->shouldReceive('hasParameter')
             ->with(Mockery::on(function ($arg) {
                 return is_string($arg);
             }))
             ->andReturn(false)
             ->getMock();
        
        $configurableHealthCheckHandler = new ConfigurableHealthCheckHandler($this->container, $this->healthCheckHandle, 'health_check.thing');

        $healthCheck = $configurableHealthCheckHandler->runHealthCheck();

        $this->assertEquals($this->healthCheck, $healthCheck);
    }

    protected function runHealthCheck() : void
    {
        Mockery::close();
    }
}
