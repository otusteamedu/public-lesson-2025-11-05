<?php

namespace App\Domain\Service\Order;

use App\Application\UseCase\CreateOrder\OrderModel;
use App\Domain\Entity\Client\ClientEntity;
use App\Domain\Entity\Order\OrderEntity;
use App\Infrastructure\Persistence\Doctrine\Order\OrderEntityRepository;

final readonly class OrderService
{
    public function __construct(
        private OrderEntityRepository $orderEntityRepository,
    ) {
    }

    public function createOrder(ClientEntity $client, array $orderContent): OrderModel
    {
        $newOrder = new OrderEntity();

        $newOrder
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($client)
            ->setStatus(OrderEntity::ORDER_STATUS_NEW)
            ->setOrderContent($orderContent);

        $this->orderEntityRepository->store($newOrder);

        return new OrderModel(
            orderId: $newOrder->getId(),
            status: $newOrder->getStatus()
        );
    }
}
