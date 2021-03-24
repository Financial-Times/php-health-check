<?php 

namespace FT\HealthCheckBundle\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use FT\HealthCheckBundle\Controller\HealthCheckController;

/**
 * This event listner is used to force health checks to run before any other user defined code is executed.
 * This can be used in cases where other event listeners that might fail in the event of degraded site health stopping health checks from properly running 
 */
class HealthCheckListener{
    const HEALTH_CHECK_ROUTE = '/__health';

    /**
     * @var HealthCheckController
     */
    protected $healthCheckController;

    public function __construct(HealthCheckController $healthCheckController){
        $this->healthCheckController = $healthCheckController;
    }

    public function onRequest(RequestEvent $event)
    {
        
        // We only care about the master request in this instance
        if(!$event->isMasterRequest()){
            return;
        }

        $request = $event->getRequest();

        //Do nothing when we are not specifically looking for the health check
        if($request->getPathInfo() !== self::HEALTH_CHECK_ROUTE){
            return;
        }
        
        //Force health check to run and immidently return
        $event->setResponse($this->healthCheckController->healthCheckAction());
    }
}