<?php
declare(strict_types=1);

namespace Integration\Fixture\Infrastructure;

use Integration\Fixture\Domain\Model\Order;
use Integration\Fixture\Domain\Orders;

final class OrderRepository implements Orders
{
    private array $orders = [];

    public function save(Order $order): void
    {
        $this->orders[] += $order;
    }
}