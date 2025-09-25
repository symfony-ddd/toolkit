<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\DependencyInjection\Compiler;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyDDD\ToolkitBundle\CommandHandler;

class CommandRouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definitions = $container->getDefinitions();

        foreach ($definitions as $id => $definition) {
            $class = $definition->getClass();

            if (!$class || !class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(CommandHandler::class);

            if (!empty($attributes)) {
                $definition->addTag('app.command_handler');
            }
        }
    }
}
