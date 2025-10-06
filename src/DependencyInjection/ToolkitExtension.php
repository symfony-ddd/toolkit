<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\DependencyInjection;

use BadMethodCallException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\MessageBus;
use SymfonyDDD\ToolkitBundle\Bridge\DefaultCommandBus;
use SymfonyDDD\ToolkitBundle\library\Command;
use SymfonyDDD\ToolkitBundle\library\CommandHandler;

class ToolkitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->registerAttributeForAutoconfiguration(
            CommandHandler::class,
            static function (ChildDefinition $definition, object $attribute, \Reflector $reflector): void {
                $tag = [];
                if (!($reflector instanceof \ReflectionMethod)) {
                    throw new BadMethodCallException(
                        '#[CommandHandler] attribute can only be used on methods. '
                    );
                }
                    
                $tag['method'] = $reflector->getName();
                
                $params = $reflector->getParameters();
                if (count($params) == 0) {
                    throw new BadMethodCallException(
                        'Command handler ' 
                        . $reflector->getName() 
                        . ' method in class ' . $reflector->class 
                        . ' must have a parameter specifying the command it handles.'
                    );
                }
                
                $type = $params[0]->getType();
                if ($type instanceof \ReflectionUnionType) {
                    throw new BadMethodCallException(
                        'Command handler '
                        . $reflector->getName()
                        . ' method in class ' . $reflector->class
                        . ' must not have single parameter type.'
                    );
                }
                if ($type instanceof \ReflectionNamedType) {
                    if (!$type->isBuiltin()) {
                        $fqcn = $type->getName();
                        if ($fqcn == Command::class) {
                            throw new BadMethodCallException(
                                'Command handler '
                                . $reflector->getName()
                                . ' method in class ' . $reflector->class
                                . ' parameter type must be final implementation of'
                                . Command::class . '.'
                            );
                        }
                        if (!is_a($fqcn, Command::class, true)) {
                            throw new BadMethodCallException(
                                'Command handler '
                                . $reflector->getName()
                                . ' method in class ' . $reflector->class
                                . ' parameter type must implement '
                                . Command::class . '.'
                            );
                        }
                        $tag['command'] = $fqcn;
                    }
                }
                $tag['manualEventRelease'] = $attribute->manualEventRelease;

                $definition->addTag('ddd_toolkit.command.handler', $tag);
            }
        );

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

        $container->setAlias('ddd_toolkit.command_bus', $commandBusServiceId);
    }

    private function configureEventBus(string $eventBusServiceId, ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($eventBusServiceId)) {
            $this->createDefaultBus($eventBusServiceId, $container);
        }

        $container->setAlias('ddd_toolkit.event_bus', $eventBusServiceId);
    }

    private function createDefaultBus(string $serviceId, ContainerBuilder $container): void
    {
        $definition = new Definition(DefaultCommandBus::class, []);

        $container->setDefinition($serviceId, $definition);
    }

    public function getAlias(): string
    {
        return 'ddd_toolkit';
    }
}
