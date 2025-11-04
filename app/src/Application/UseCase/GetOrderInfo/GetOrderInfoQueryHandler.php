<?php

namespace App\Application\UseCase\GetOrderInfo;

use App\Domain\Entity\Order\OrderEntity;
use App\Infrastructure\Persistence\Doctrine\Order\OrderEntityRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetOrderInfoQueryHandler
{
    public function __construct(
        private OrderEntityRepository $orderEntityRepository,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function __invoke(GetOrderInfoQuery $query): OrderInfoModel
    {
        /** @var OrderEntity $order */
        $order = $this->orderEntityRepository->find($query->getOrderId());

        if (empty($order)) {
            throw new BadRequestHttpException('Order not found');
        }

        return $this->objectMapper->map($order, OrderInfoModel::class);
    }
}