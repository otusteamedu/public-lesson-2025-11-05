<?php

namespace App\Application\UseCase\GetOrderInfo;

use App\Application\UseCase\CreateOrder\OrderModel;

final readonly class GetOrderInfoQuery
{
    public function __construct(private string $orderId)
    {
    }

    public static function fromModel(OrderModel $model): self
    {
        return new self($model->getOrderId());
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}