<?php
declare(strict_types=1);

namespace Integration\Fixture\Domain;

use Integration\Fixture\Domain\Model\Order;

interface Orders
{
    public function save(Order $order): void;
}