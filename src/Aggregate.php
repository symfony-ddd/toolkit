<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle;

use SymfonyDDD\ToolkitBundle\library\AggregateRoot;
use SymfonyDDD\ToolkitBundle\library\DomainEvent;

abstract class Aggregate implements AggregateRoot
{
    /**
     * If the aggregate needs to be serialized, a custom implementation of AggregateRootInterface could be made
     * @JMS\Exclude
     * @var array<DomainEvent>
     */
    private array $events = [];

    /**
     * @return array<DomainEvent>
     */
    final public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    protected function recordThat(DomainEvent $happened): void
    {
        $this->events[] = $happened;
    }
}