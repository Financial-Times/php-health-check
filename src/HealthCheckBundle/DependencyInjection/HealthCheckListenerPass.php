<?php
namespace FT\HealthCheckBundle\DependencyInjection;

use FT\HealthCheckBundle\EventListener\HealthCheckListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\KernelEvents;

class HealthCheckListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //Check for if we are forcing the __health endpoint to run before anything else
        if($container->hasParameter('health_check.run_first') && $container->getParameter('health_check.run_first') === true){

            //Take user defined priority in the event that this should be run behind other events
            $priority = $container->hasParameter('health_check.run_first.priority') ? $container->getParameter('health_check.run_first.priority') : 255;
            
            //Inject listener that should trigger before all other event bindings
            $container->register('health_check.event.pre_request_event_subscriber', HealthCheckListener::class)
                ->addArgument(new Reference('health_check.controller'))
                ->addTag('kernel.event_listener', ['event' => KernelEvents::REQUEST, 'method' => 'onRequest',  'priority' => $priority]);
        }
    }
}