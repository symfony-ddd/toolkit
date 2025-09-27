<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use SymfonyDDD\ToolkitBundle\Bus\EventBus;
use SymfonyDDD\ToolkitBundle\Cqrs\CommandBus;

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

    $services->alias(CommandBus::class, 'ddd_toolkit.command_bus')
        ->public(false);

    $services->alias(EventBus::class, 'ddd_toolkit.event_bus')
        ->public(false);
};
