<?php

namespace SymfonyDDD\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use SymfonyDDD\CoreBundle\DependencyInjection\CoreExtension;

class CoreBundle extends AbstractBundle
{
    public function getContainerExtension(): ?CoreExtension
    {
        return new CoreExtension();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
    }
}
