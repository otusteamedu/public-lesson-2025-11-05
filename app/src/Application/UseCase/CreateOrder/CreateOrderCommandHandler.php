<?php

namespace App\Application\UseCase\CreateOrder;

use App\Domain\Entity\Client\ClientEntity;
use App\Domain\Service\Order\OrderService;
use App\Infrastructure\Persistence\Doctrine\Client\ClientEntityRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateOrderCommandHandler
{
    public function __construct(
        private OrderService $orderService,
        private ClientEntityRepository $clientEntityRepository
    ) {
    }

    public function __invoke(CreateOrderCommand $command): OrderModel
    {
        /** @var ClientEntity $client */
        $client = $this->clientEntityRepository->findOneBy(['id' => $command->clientId]);

        return $this->orderService->createOrder($client, $command->orderContent);
    }
}
