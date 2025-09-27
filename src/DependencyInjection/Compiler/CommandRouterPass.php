<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\DependencyInjection\Compiler;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use SymfonyDDD\ToolkitBundle\library\AggregateRoot;
use SymfonyDDD\ToolkitBundle\library\Command;
use SymfonyDDD\ToolkitBundle\library\CommandHandler;

class CommandRouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ddd_toolkit.command_router')) {
            return;
        }

        $definitions = $container->getDefinitions();
        $handlerMap = [];
        $commandClasses = [];

        // First pass: collect all command handlers
        foreach ($definitions as $id => $definition) {
            $class = $definition->getClass();

            if (!$class || !class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->implementsInterface(CommandHandler::class)) {
                $commandClass = $this->determineCommandClass($reflection);

                if (isset($handlerMap[$commandClass])) {
                    throw new RuntimeException(
                        sprintf('Handler for command %s is already registered', $commandClass)
                    );
                }

                $handlerMap[$commandClass] = new Reference($id);
                $definition->addTag('app.command_handler');
            }

            if ($reflection->isSubclassOf(Command::class) && !$reflection->isAbstract()) {
                $commandClasses[] = $class;
            }
        }

        foreach ($commandClasses as $commandClass) {
            if (!isset($handlerMap[$commandClass])) {
                throw new RuntimeException(
                    sprintf('No handler found for command: %s', $commandClass)
                );
            }
        }

        $routerDefinition = $container->getDefinition('ddd_toolkit.command_router');
        $routerDefinition->setArgument('$handlerMap', $handlerMap);
    }

    /**
     * @param ReflectionClass<CommandHandler> $reflection
     */
    private function determineCommandClass(ReflectionClass $reflection): string
    {
        $invokeMethod = $reflection->getMethod('__invoke');
        $parameter = $invokeMethod->getParameters()[0];
        $parameterType = $parameter->getType();

        return $parameterType->getName();
    }
}
