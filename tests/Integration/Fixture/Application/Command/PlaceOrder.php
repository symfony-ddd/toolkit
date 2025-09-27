<?php

namespace Integration\Fixture\Application\Command;

use Integration\Fixture\Domain\Value\OrderId;
use SymfonyDDD\ToolkitBundle\library\Command;

final readonly class PlaceOrder implements Command
{
    public function __construct(
        public OrderId $orderId,
    )
    {
    }
}