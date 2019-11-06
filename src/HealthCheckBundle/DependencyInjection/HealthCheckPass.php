<?php

namespace FT\HealthCheckBundle\DependencyInjection;

use FT\HealthCheckBundle\EventListener\HealthCheckListener;
use FT\HealthCheckBundle\HealthCheck\ConfigurableHealthCheckHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\KernelEvents;

class HealthCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('health_check.controller')) {
            return;
        }
        $healthCheckController = $container->getDefinition('health_check.controller');

        // Get health checks.
        $healthChecks = $container->findTaggedServiceIds('health_check');

        // Get Configurable healthchecks
        $configurableHealthChecks = $container->findTaggedServiceIds('health_check.configurable');
        
        // Terminate compiler pass if there are no healthchecks to register
        if(empty($healthChecks) && empty($configurableHealthChecks)){
            return;
        }

        $converterIdsByPriority = [];

        foreach ($configurableHealthChecks as $id => $tags) {
            //Create and inject a decorated service definition each definition tagged with a configurable health check
            $container
                ->register($id.'.decorated', ConfigurableHealthCheckHandler::class)
                ->addArgument(new Reference('service_container'))
                ->addArgument(new Reference($id.".decorated.inner"))
                ->addArgument($id)
                ->setDecoratedService($id);
            
            //Try to pull service priority from a user set priority
            $priority = $container->hasParameter($id.'.priority') ? $container->getParameter($id.'.priority') : null;
            
            //In the event that that fails or the priority is invalid use the original services priority
            if (!is_int($priority)) {
                foreach ($tags as $tag) {
                    $priority = isset($tag['priority']) ? (int) $tag['priority'] : 0;
                }
            }

            $converterIdsByPriority[$priority][] = $id;
        }

        foreach ($healthChecks as $id => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? (int) $tag['priority'] : 0;
                $converterIdsByPriority[$priority][] = $id;
            }
        }
        $converterIdsByPriority = $this->sortConverterIds($converterIdsByPriority);
        foreach ($converterIdsByPriority as $referenceId) {
            $healthCheckController->addMethodCall('addHealthCheck', array(new Reference($referenceId)));
        }
    }
    
    /**
     * Transforms a two-dimensional array of converters, indexed by priority,
     * into a flat array of Reference objects.
     *
     * @param array $converterIdsByPriority
     *
     * @return string[]
     */
    protected function sortConverterIds(array $converterIdsByPriority)
    {
        asort($converterIdsByPriority);
        return call_user_func_array('array_merge', $converterIdsByPriority);
    }
}
