<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyDDD\ToolkitBundle\AggregateRoot;

class ExcludeAggregatesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();

            if ($class && is_subclass_of($class, AggregateRoot::class)) {
                $container->removeDefinition($id);
            }
        }

        foreach ($container->getAliases() as $id => $alias) {
            $definition = $container->findDefinition((string)$alias);
            $class = $definition->getClass();

            if ($class && is_subclass_of($class, AggregateRoot::class)) {
                $container->removeAlias($id);
            }
        }
    }
}
