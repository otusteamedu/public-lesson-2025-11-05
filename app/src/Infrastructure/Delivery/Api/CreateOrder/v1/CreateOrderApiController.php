<?php

namespace App\Infrastructure\Delivery\Api\CreateOrder\v1;

use App\Domain\Entity\Client\ClientEntity;
use App\Domain\Entity\Order\OrderEntity;
use App\Domain\Response\ApiResponseInterface;
use App\Domain\Response\SuccessResponse;
use App\Infrastructure\Delivery\Api\CreateOrder\v1\Request\CreateOrderDto;
use App\Infrastructure\Delivery\Api\CreateOrder\v1\Request\CreateOrderValueResolver;
use App\Infrastructure\Persistence\Doctrine\Client\ClientEntityRepository;
use App\Infrastructure\Persistence\Doctrine\Order\OrderEntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class CreateOrderApiController
{
    public function __construct(
        private ClientEntityRepository $clientEntityRepository,
        private OrderEntityRepository $orderEntityRepository,
    ) {
    }

    #[Route('/api/v1/create-order', name: 'api_create_order_v1', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload(resolver: CreateOrderValueResolver::class)] CreateOrderDto $createOrderDto
    ): ApiResponseInterface {
        /** @var ClientEntity $client */
        $client = $this->clientEntityRepository->findOneBy(['id' => $createOrderDto->clientId]);

        if (empty($client)) {
            throw new BadRequestHttpException('Client not found');
        }

        $newOrder = new OrderEntity();

        $newOrder
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($client)
            ->setStatus(OrderEntity::ORDER_STATUS_NEW)
            ->setOrderContent($createOrderDto->orderContent);

        $this->orderEntityRepository->store($newOrder);

        return new SuccessResponse(
            data: [
                'orderId' => $newOrder->getId(),
                'status' => $newOrder->getStatus(),
            ],
            message: null,
            resultCode: Response::HTTP_CREATED
        );
    }
}
