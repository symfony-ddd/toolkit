<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\library;

interface CommandHandler
{
    public function __invoke(Command $command): AggregateRoot;
}
