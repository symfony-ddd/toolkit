<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Bridge;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SymfonyDDD\ToolkitBundle\Bus\CommandBus;
use SymfonyDDD\ToolkitBundle\library\Command;

final class DefaultCommandBus implements CommandBus
{
    /**
     * @param array<class-string, callable>  $handlers
     */
    public function __construct(
        #[Autowire(service: 'ddd_toolkit.command.handler')]
        private readonly array $handlers = [],
    ) {
    }

    public function dispatch(Command $command): void
    {
        ($this->handlers[get_class($command)])($command);
    }
}