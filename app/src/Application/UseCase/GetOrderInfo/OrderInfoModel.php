<?php

namespace App\Application\UseCase\GetOrderInfo;

final readonly class OrderInfoModel
{
    public function __construct(
        private readonly int $id,
        private readonly \DateTime $createdAt,
        private readonly string $status,
        private readonly array $orderContent
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOrderContent(): array
    {
        return $this->orderContent;
    }
}