<?php

namespace App\Infrastructure\Delivery\Api\CreateOrder\v1;

use App\Domain\Entity\Client\ClientEntity;
use App\Domain\Entity\Order\OrderEntity;
use App\Infrastructure\Persistence\Doctrine\Client\ClientEntityRepository;
use App\Infrastructure\Persistence\Doctrine\Order\OrderEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function __invoke(Request $request): JsonResponse
    {
        $content = $request->getContent();

        $orderData = json_decode($content, true);

        if (empty($orderData)) {
            throw new BadRequestHttpException('Order data is empty');
        }

        $clientId = $orderData['clientId'] ?? null;
        $orderContent = $orderData['orderContent'] ?? null;

        if (empty($orderContent)) {
            throw new BadRequestHttpException('Order content is empty');
        }

        /** @var ClientEntity $client */
        $client = $this->clientEntityRepository->findOneBy(['id' => $clientId]);

        if (empty($client)) {
            throw new BadRequestHttpException('Client not found');
        }

        $newOrder = new OrderEntity();

        $newOrder
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($client)
            ->setStatus(OrderEntity::ORDER_STATUS_NEW)
            ->setOrderContent($orderContent);

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
