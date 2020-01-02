<?php
namespace FT\Tests\HealthCheckBundle\EventListener;

use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use FT\HealthCheckBundle\Controller\HealthCheckController;
use FT\HealthCheckBundle\EventListener\HealthCheckListener;

/**
 * @coversDefaultClass \FT\HealthCheckBundle\EventListener\HealthCheckListener
 */
class HealthCheckListenerTest extends TestCase{
    /**
     * The mock health check controller
     *
     * @var Mockery
     */
    protected $healthCheckController;

    protected function setUp(): void{
        $this->healthCheckController = Mockery::mock(HealthCheckController::class);
    }

    /**
     * Tests that the master request will not do anything in the event that the request is a sub request
     *
     * @covers ::__construct
     * @covers ::onRequest
     */
    public function test_onRequest_onlyAppliesToTheMasterRequest(){
        $getResponseEvent = Mockery::mock(GetResponseEvent::class);
        $getResponseEvent
        //Handle request for if event is master company
            ->shouldReceive('isMasterRequest')
            ->andReturn(false)
            ->getMock();

        $healthCheckListener = new HealthCheckListener($this->healthCheckController);

        $this->assertNull($healthCheckListener->onRequest($getResponseEvent));
    }

    /**
     * Tests that various paths do not match the expected route
     *
     * @covers ::__construct
     * @covers ::onRequest
     */
    public function test_onRequest_pathsShouldNotMatch(){
        $pathsThatShouldNotMatch = [
            '/some/long/path',
            '/aSinglePartPath',
            '/',
            '/__service',
            '/__health\/',
            '/__health/stuff'
        ];

        foreach ($pathsThatShouldNotMatch as $path) {
            $request = Mockery::mock(Request::class);

            $request
                // Handle call to retrieval of path info from current master request
                ->shouldReceive('getPathInfo')
                ->andReturn($path)
                ->getMock();

            $getResponseEvent = Mockery::mock(GetResponseEvent::class);
            $getResponseEvent
                //Handle request for if event is master company
                ->shouldReceive('isMasterRequest')
                ->andReturn(true)
                // Get the current request
                ->shouldReceive('getRequest')
                ->andReturn($request)
                ->getMock();
            
            $healthCheckListener = new HealthCheckListener($this->healthCheckController);

            $this->assertNull($healthCheckListener->onRequest($getResponseEvent));
        }
    }

    /**
     * Tests that requests are properly intercepted by any given request listener
     *
     * @covers ::__construct
     * @covers ::onRequest
     */
    public function test_onRequest_matchingMasterRequestPathShouldHijackRequest(){
        $response = new Response('Some response data');
        $request = Mockery::mock(Request::class);

        $request
            ->shouldReceive('getPathInfo')
            ->andReturn('/__health')
            ->getMock();

        $getResponseEvent = Mockery::mock(GetResponseEvent::class);
        $getResponseEvent
            // Handle request for if event is master company
            ->shouldReceive('isMasterRequest')
            ->andReturn(true)
            // Get the current request
            ->shouldReceive('getRequest')
            ->andReturn($request)
            // Handle setting response
            ->shouldReceive('setResponse')
            ->with($response)
            ->getMock();
        
        $this->healthCheckController
            // Handle controller execution for setting the request
            ->shouldReceive('healthCheckAction')
            ->andReturn($response);
        
        $healthCheckListener = new HealthCheckListener($this->healthCheckController);

        $this->assertNull($healthCheckListener->onRequest($getResponseEvent));
    
    }
    
    protected function runHealthCheck() : void {
        Mockery::close();
    }
}