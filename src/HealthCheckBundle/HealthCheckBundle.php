<?php

namespace FT\HealthCheckBundle;

use FT\HealthCheckBundle\DependencyInjection\HealthCheckPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HealthCheckBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new HealthCheckPass());
    }
}
