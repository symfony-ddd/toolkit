<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ddd_toolkit');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('buses')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('command_bus')
                        ->end()
                        ->scalarNode('event_bus')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
