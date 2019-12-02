<?php

namespace FT\HealthCheckBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FT\HealthCheckBundle\DependencyInjection\HealthCheckPass;
use FT\HealthCheckBundle\DependencyInjection\HealthCheckListenerPass;

class HealthCheckBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new HealthCheckPass());
        $container->addCompilerPass(new HealthCheckListenerPass());
    }
}
