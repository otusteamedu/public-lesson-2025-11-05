<?php

namespace App\Infrastructure\Delivery\Api\CreateOrder\v1;

use App\Application\CQRS\CQRSTrait;
use App\Application\UseCase\CreateOrder\CreateOrderCommand;
use App\Application\UseCase\CreateOrder\OrderModel;
use App\Application\UseCase\GetOrderInfo\GetOrderInfoQuery;
use App\Application\UseCase\GetOrderInfo\OrderInfoModel;
use App\Infrastructure\Delivery\Api\CreateOrder\v1\Request\CreateOrderValueResolver;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CreateOrderApiController
{
    use CQRSTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus,
    ) {
    }

    /**
     * @param CreateOrderCommand $createOrderCommand
     * @return OrderInfoModel
     *
     * @throws ExceptionInterface
     */
    #[Route('/api/v1/create-order', name: 'api_create_order_v1', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload(resolver: CreateOrderValueResolver::class)]
        CreateOrderCommand $createOrderCommand
    ): OrderInfoModel {
        /** @var OrderModel $orderModel */
        $orderModel = $this->handleCommand($createOrderCommand);

        return $this->handleQuery(GetOrderInfoQuery::fromModel($orderModel));
    }
}
