<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Bus;

use SymfonyDDD\ToolkitBundle\library\Command;

interface CommandBus
{
    public function dispatch(Command $command): void;
}
