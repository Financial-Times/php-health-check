<?php 

namespace FT\HealthCheckBundle\EventListener;

use Exception;
use FT\HealthCheckBundle\Controller\HealthCheckController;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

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

    public function onRequest(GetResponseEvent $event)
    {
        
        // We only care about the master request in this instance
        if(!$event->isMasterRequest()){
            return;
        }

        $request = $event->getRequest();

        //Do nothing when we are not specifically looking for the health check
        if(substr($request->getPathInfo(), 0 , strlen(self::HEALTH_CHECK_ROUTE)) !== self::HEALTH_CHECK_ROUTE){
            return;
        }

        //Try to get the health check id if we are using (+ 1 to ignore the trailing slash /__health/)
        $healthCheckId = substr($request->getPathInfo(), strlen(self::HEALTH_CHECK_ROUTE) + 1);
        
        if($healthCheckId === false || $healthCheckId === ""){
            //Force health check to run and immediately return
            $event->setResponse($this->healthCheckController->healthCheckAction());
        } else {
            // Get by id if an id is given
            $event->setResponse($this->healthCheckController->healthCheckByIdAction($healthCheckId));
        }
    }
}