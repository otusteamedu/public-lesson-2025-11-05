# Делаем тонкие контроллеры на Symfony

## Сборка проекта и зависимостей

1. Запускаем контейнеры командой `docker-compose up -d`
2. Подключаемся к контейнеру `pl-php-fpm`: `docker exec -it pl-php-fpm bash`

Дальнейшие команды выполняются в подключённом контейнере

1. Устанавливаем зависимости: выполняем команду `composer install`
2. Исполняем стартовую миграцию: выполняем команду `php bin/console doctrine:migrations:migrate`, соглашаемся с вопросом о выполнении миграций

Параметры БД можно посмотреть в файле `docker-compose.yaml` для контейнера `pl-postgres`.
После миграций должны создаться две таблицы в БД: `client` и `order`.
В таблице `client` должна быть одна запись.

## Проверяем работоспособность

## Успешный запрос. Создание новой записи
Выполняем из Postman-коллекции `Otus-public-lesson-2025-11-05` запрос `before/OK /api/create-order/v1`.
Убеждаемся, что в БД, в таблице `order` добавилась новая запись.

## Неуспешный запрос. Ошибка 404
Выполняем из Postman-коллекции `Otus-public-lesson-2025-11-05` запрос `404 /api/not-existing-endpoint`.
Видим стандартную ошибку Symfony с кодом 404.

## Десериализация входящих данных
1. Устанавливаем пакет `composer require symfony/serializer-pack`
2. Создаём DTO `App\Infrastructure\Delivery\Api\CreateOrder\v1\Request\CreateOrderDto`
    ```php
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
   ```
3. Исправляем контроллер `App\Infrastructure\Delivery\Api\CreateOrder\v1\CreateOrderApiController`
   ```php
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
           if (empty($createOrderDto->orderContent)) {
               throw new BadRequestHttpException('Order content is empty');
           }
   
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
   ```
   
## Добавляем валидацию
1. Устанавливаем пакет `composer require symfony/validator`
2. Исправляем DTO `App\Infrastructure\Delivery\Api\CreateOrder\v1\Request\CreateOrderDto`
   ```php
   <?php
   
   namespace App\Infrastructure\Delivery\Api\CreateOrder\v1\Request;
   
   use Symfony\Component\Validator\Constraints as Assert;
   
   final readonly class CreateOrderDto
   {
       public function __construct(
           #[Assert\Positive(message: 'Client Id must be greater than 0')]
           public int $clientId,
   
           #[Assert\NotBlank(message: 'Order content must containt at least one order item')]
           public array $orderContent
       ) {
       }
   }
   ```
3. Исправляем класс `App\Infrastructure\Delivery\Api\CreateOrder\v1\CreateOrderApiController`
   ```php
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
   ```
3. Выполняем запрос из Postman-коллекции `before/OK /api/create-order/v1`. Видим, что ещё один заказ успешно создан
4. Выполняем запрос из Postman-коллекции `common/422 Empty orderContent /api/create-order/v1` с пустым содержимым заказа. Видим ошибку 422
4. Выполняем запрос из Postman-коллекции `common/422 zeroed client id /api/create-order/v1` с нулевым значением `clientId`. Видим ошибку 422