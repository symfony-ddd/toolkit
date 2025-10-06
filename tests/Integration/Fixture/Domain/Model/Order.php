<?php
declare(strict_types=1);

namespace Integration\Fixture\Domain\Model;

use DomainException;
use Integration\Fixture\Domain\Value\OrderId;
use Integration\Fixture\Domain\Value\Placement;
use SymfonyDDD\ToolkitBundle\Aggregate;

class Order extends Aggregate
{
    private OrderId $orderId;
    private Placement $placed;

    public function __construct(
        OrderId $orderId
    )
    {
        $this->placed = Placement::uncommited();
    }

    public function place(): void
    {
        if ($this->placed->isCommited()) {
            return;
        }

        $this->placed = Placement::commited();

        $this->recordThat();
    }

}