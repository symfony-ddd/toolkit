<?php

namespace SymfonyDDD\CoreBundle\Domain;

interface AggregateRoot
{
    /**
     * @return array<DomainEvent>
     */
    public function releaseEvents(): array;
}