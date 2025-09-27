<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\library;

interface AggregateRoot
{
    /**
     * @return array<DomainEvent>
     */
    public function releaseEvents(): array;
}