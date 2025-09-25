<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\MessageBus;

class ToolkitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $servicesFile = __DIR__ . '/../../config/services.php';
        if (file_exists($servicesFile)) {
            $loader = new PhpFileLoader(
                $container,
                new FileLocator(__DIR__ . '/../../config')
            );
            $loader->load('services.php');
        }

        $this->configureBuses($config['buses'], $container);
    }

    private function configureBuses(array $busesConfig, ContainerBuilder $container): void
    {
        $this->configureCommandBus($busesConfig['command_bus'], $container);
        $this->configureEventBus($busesConfig['event_bus'], $container);
    }

    private function configureCommandBus(string $commandBusServiceId, ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($commandBusServiceId)) {
            $this->createDefaultBus($commandBusServiceId, $container);
        }

        $container->setAlias('symfony_ddd_toolkit.command_bus', $commandBusServiceId);
    }

    private function createDefaultBus(string $serviceId, ContainerBuilder $container): void
    {
        $definition = new Definition(MessageBus::class, []);
        $definition->addTag('messenger.bus');

        $container->setDefinition($serviceId, $definition);
    }

    private function configureEventBus(string $eventBusServiceId, ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($eventBusServiceId)) {
            $this->createDefaultBus($eventBusServiceId, $container);
        }

        $container->setAlias('symfony_ddd_toolkit.event_bus', $eventBusServiceId);
    }

    public function getAlias(): string
    {
        return 'symfony_ddd_toolkit';
    }
}
