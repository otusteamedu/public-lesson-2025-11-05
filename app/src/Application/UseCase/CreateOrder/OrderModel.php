<?php

namespace App\Application\UseCase\CreateOrder;

final readonly class OrderModel
{
    public function __construct(
        private int $orderId,
        private string $status
    ) {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}