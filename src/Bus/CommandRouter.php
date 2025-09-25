<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Bus;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use SymfonyDDD\ToolkitBundle\AggregateRoot;
use SymfonyDDD\ToolkitBundle\Command;
use SymfonyDDD\ToolkitBundle\CommandHandler;

#[AsMessageHandler]
class CommandRouter
{
    private array $handlers = [];

    public function __construct(
        #[TaggedIterator('app.command_handler')] iterable $commandHandlers
    ) {
        $this->registerHandlers($commandHandlers);
    }

    public function __invoke(Command $command): AggregateRoot
    {
        $commandClass = get_class($command);

        if (!isset($this->handlers[$commandClass])) {
            throw new RuntimeException(
                sprintf('No handler found for command: %s', $commandClass)
            );
        }

        $handler = $this->handlers[$commandClass];

        return $handler($command);
    }

    private function registerHandlers(iterable $commandHandlers): void
    {
        foreach ($commandHandlers as $handler) {
            $this->validateAndRegisterHandler($handler);
        }
    }

    private function validateAndRegisterHandler(object $handler): void
    {
        $reflection = new ReflectionClass($handler);

        $attributes = $reflection->getAttributes(CommandHandler::class);
        if (empty($attributes)) {
            throw new RuntimeException(
                sprintf('Handler %s must have #[CommandHandler] attribute', $reflection->getName())
            );
        }

        if (!$reflection->hasMethod('__invoke')) {
            throw new RuntimeException(
                sprintf('Handler %s must have __invoke method', $reflection->getName())
            );
        }

        $invokeMethod = $reflection->getMethod('__invoke');
        $this->validateInvokeMethod($invokeMethod, $reflection->getName());

        // Determine command class from method parameter type
        $commandClass = $this->determineCommandClass($invokeMethod);

        if (isset($this->handlers[$commandClass])) {
            throw new RuntimeException(
                sprintf('Handler for command %s is already registered', $commandClass)
            );
        }

        $this->handlers[$commandClass] = $handler;
    }

    private function validateInvokeMethod(ReflectionMethod $method, string $handlerClass): void
    {
        // Check return type is AggregateRoot
        $returnType = $method->getReturnType();
        if (!$returnType || !is_a($returnType->getName(), AggregateRoot::class, true)) {
            throw new RuntimeException(
                sprintf(
                    'Handler %s::__invoke() must return %s',
                    $handlerClass,
                    AggregateRoot::class
                )
            );
        }

        // Check has exactly one parameter
        $parameters = $method->getParameters();
        if (count($parameters) !== 1) {
            throw new RuntimeException(
                sprintf(
                    'Handler %s::__invoke() must have exactly one parameter', 
                    $handlerClass
                )
            );
        }

        // Check parameter has a type
        $parameter = $parameters[0];
        $parameterType = $parameter->getType();
        if (!$parameterType || $parameterType->isBuiltin()) {
            throw new RuntimeException(
                sprintf(
                    'Handler %s::__invoke() parameter must have a command type', 
                    $handlerClass
                )
            );
        }
    }

    private function determineCommandClass(ReflectionMethod $method): string
    {
        // Get command class from method parameter type
        $parameter = $method->getParameters()[0];
        $parameterType = $parameter->getType();

        return $parameterType->getName();
    }
}
