<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfony_ddd_toolkit');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('buses')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('command_bus')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('service_id')
                                    ->defaultValue('messenger.bus.commands')
                                    ->info('Service ID of the command bus')
                                ->end()
                                ->arrayNode('middleware')
                                    ->scalarPrototype()->end()
                                    ->defaultValue([])
                                    ->info('Additional middleware for command bus')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('event_bus')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('service_id')
                                    ->defaultValue('messenger.bus.events')
                                    ->info('Service ID of the event bus')
                                ->end()
                                ->arrayNode('middleware')
                                    ->scalarPrototype()->end()
                                    ->defaultValue([])
                                    ->info('Additional middleware for event bus')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
