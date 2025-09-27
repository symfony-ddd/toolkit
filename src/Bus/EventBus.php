<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Bus;

use SymfonyDDD\ToolkitBundle\library\DomainEvent;

interface EventBus
{
    public function dispatch(DomainEvent $event): void;
}
