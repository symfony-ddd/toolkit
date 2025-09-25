<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle;

interface AggregateRoot
{
    /**
     * @return array<DomainEvent>
     */
    public function releaseEvents(): array;
}