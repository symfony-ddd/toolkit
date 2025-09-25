<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use SymfonyDDD\ToolkitBundle\Bus\CommandBus;
use SymfonyDDD\ToolkitBundle\Bus\CommandRouter;
use SymfonyDDD\ToolkitBundle\Bus\EventBus;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    $services->load('SymfonyDDD\\ToolkitBundle\\', '../src/')
        ->exclude([
            '../src/ToolkitBundle.php',
            '../src/DependencyInjection/',
            '../src/ValueObject/',
            '../src/Exception/',
        ]);

    // Register the command router
    $services->set(CommandRouter::class)
        ->arg('$commandHandlers', tagged_iterator('app.command_handler'))
        ->tag('messenger.message_handler', ['bus' => 'symfony_ddd_toolkit.command_bus']);

    // Aliases for easy access to configured buses
    $services->alias(CommandBus::class, 'symfony_ddd_toolkit.command_bus')
        ->public(false);

    $services->alias(EventBus::class, 'symfony_ddd_toolkit.event_bus')
        ->public(false);
};
