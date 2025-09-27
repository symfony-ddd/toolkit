<?php

namespace Integration\Fixture\Application\CommandHandler;

use Integration\Fixture\Application\Command\PlaceOrder;
use Integration\Fixture\Domain\Model\Order;
use Integration\Fixture\Domain\Orders;
use SymfonyDDD\ToolkitBundle\library\CommandHandler;

final readonly class PlaceOrderHandler
{
    public function __construct(
        private readonly Orders $orders,
    ){
    }

    #[CommandHandler]
    public function __invoke(PlaceOrder $command): Order
    {
        $order = new Order($command->orderId);
        $order->place();
        $this->orders->save($order);
        
        return $order;
    }
}