<?php

namespace FT\HealthCheckBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
        $converterIdsByPriority = array();
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
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function sortConverterIds(array $converterIdsByPriority)
    {
        asort($converterIdsByPriority);
        return call_user_func_array('array_merge', $converterIdsByPriority);
    }
}
