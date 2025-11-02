<?php

namespace App\Infrastructure\Delivery\Api\CreateOrder\v1\Request;

final readonly class CreateOrderDto
{
    public function __construct(
        public int $clientId,
        public array $orderContent
    ) {
    }
}