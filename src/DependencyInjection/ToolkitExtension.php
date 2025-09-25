<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;

class ToolkitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.php');

        $this->configureBuses($config['buses'], $container);
    }

    public function getAlias(): string
    {
        return 'symfony_ddd_toolkit';
    }

    private function configureBuses(array $busesConfig, ContainerBuilder $container): void
    {
        $this->configureCommandBus($busesConfig['command_bus'], $container);
        $this->configureEventBus($busesConfig['event_bus'], $container);
    }

    private function configureCommandBus(array $commandBusConfig, ContainerBuilder $container): void
    {
        $serviceId = $commandBusConfig['service_id'];

        if (!$container->hasDefinition($serviceId)) {
            $this->createDefaultCommandBus($serviceId, $commandBusConfig['middleware'], $container);
        }

        $container->setAlias('symfony_ddd_toolkit.command_bus', $serviceId);
    }

    private function configureEventBus(array $eventBusConfig, ContainerBuilder $container): void
    {
        $serviceId = $eventBusConfig['service_id'];

        if (!$container->hasDefinition($serviceId)) {
            $this->createDefaultEventBus($serviceId, $eventBusConfig['middleware'], $container);
        }

        $container->setAlias('symfony_ddd_toolkit.event_bus', $serviceId);
    }

    private function createDefaultCommandBus(string $serviceId, array $additionalMiddleware, ContainerBuilder $container): void
    {
        $middleware = [
            new Reference('messenger.middleware.send_message'),
            new Reference('messenger.middleware.handle_message'),
        ];

        foreach ($additionalMiddleware as $middlewareService) {
            array_unshift($middleware, new Reference($middlewareService));
        }

        $definition = new Definition(MessageBus::class, [$middleware]);
        $definition->addTag('messenger.bus');

        $container->setDefinition($serviceId, $definition);

        $this->ensureDefaultMiddleware($container);
    }

    private function createDefaultEventBus(string $serviceId, array $additionalMiddleware, ContainerBuilder $container): void
    {
        $middleware = [
            new Reference('messenger.middleware.send_message'),
            new Reference('messenger.middleware.handle_message'),
        ];

        foreach ($additionalMiddleware as $middlewareService) {
            array_unshift($middleware, new Reference($middlewareService));
        }

        $definition = new Definition(MessageBus::class, [$middleware]);
        $definition->addTag('messenger.bus');

        $container->setDefinition($serviceId, $definition);

        $this->ensureDefaultMiddleware($container);
    }

    private function ensureDefaultMiddleware(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('messenger.middleware.send_message')) {
            $definition = new Definition(SendMessageMiddleware::class);
            $container->setDefinition('messenger.middleware.send_message', $definition);
        }

        if (!$container->hasDefinition('messenger.middleware.handle_message')) {
            $definition = new Definition(HandleMessageMiddleware::class);
            $container->setDefinition('messenger.middleware.handle_message', $definition);
        }
    }
}
