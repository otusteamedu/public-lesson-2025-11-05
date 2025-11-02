<?php

namespace App\Infrastructure\Delivery\Api\CreateOrder\v1;

use App\Domain\Entity\Client\ClientEntity;
use App\Domain\Entity\Order\OrderEntity;
use App\Infrastructure\Delivery\Api\CreateOrder\v1\Request\CreateOrderDto;
use App\Infrastructure\Persistence\Doctrine\Client\ClientEntityRepository;
use App\Infrastructure\Persistence\Doctrine\Order\OrderEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class CreateOrderApiController extends AbstractController
{
    public function __construct(
        private readonly ClientEntityRepository $clientEntityRepository,
        private readonly OrderEntityRepository $orderEntityRepository,
    ) {
    }

    #[Route('/api/v1/create-order', name: 'api_create_order_v1', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] CreateOrderDto $createOrderDto): JsonResponse
    {
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

        return $this->json(
            data: [
                'success' => true,
                'message' => null,
                'data' => [
                    'orderId' => $newOrder->getId(),
                    'status' => $newOrder->getStatus(),
                ]
            ],
            status: Response::HTTP_CREATED
        );
    }
}
