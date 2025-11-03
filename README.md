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
Выполняем из Postman-коллекции `Otus-public-lesson-2025-11-05` запрос `OK/OK /api/create-order/v1`.
Убеждаемся, что в БД, в таблице `order` добавилась новая запись.

## Неуспешный запрос. Ошибка 404
Выполняем из Postman-коллекции `Otus-public-lesson-2025-11-05` запрос `Errors/404 /api/not-existing-endpoint`.
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
4. Выполняем запрос из Postman-коллекции `OK/OK /api/create-order/v1`. Видим, что ещё один заказ успешно создан
5. Выполняем запрос из Postman-коллекции `Errors/422 Empty orderContent /api/create-order/v1` с пустым содержимым заказа. Видим ошибку 422 от валидатора
6. Выполняем запрос из Postman-коллекции `Errors/422 zeroed client id /api/create-order/v1` с нулевым значением `clientId`. Видим ошибку 422 от валидатора

## Добавляем кастомный разбор входящего запроса
1. Исправляем DTO `App\Infrastructure\Delivery\Api\CreateOrder\v1\Request\CreateOrderDto`
   ```php
   <?php
   
   namespace App\Infrastructure\Delivery\Api\CreateOrder\v1\Request;
   
   use Symfony\Component\Validator\Constraints as Assert;
   
   final class CreateOrderDto
   {
       public function __construct(
           public ?string $_source,
   
           #[Assert\Positive(message: 'Client Id must be greater than 0')]
           public int $clientId,
   
           #[Assert\NotBlank(message: 'Order content must containt at least one order item')]
           public array $orderContent
       ) {
       }
   }
   ```
2. Добавляем резолвер `App\Infrastructure\Delivery\Api\CreateOrder\v1\Request\CreateOrderValueResolver`
   ```php
   <?php
   
   namespace App\Infrastructure\Delivery\Api\CreateOrder\v1\Request;
   
   use App\Domain\Exception\ApiValidationException;
   use Symfony\Component\HttpFoundation\Request;
   use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
   use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
   use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
   use Symfony\Component\Serializer\Exception\ExceptionInterface;
   use Symfony\Component\Serializer\SerializerInterface;
   use Symfony\Component\Validator\Validator\ValidatorInterface;
   
   final readonly class CreateOrderValueResolver implements ValueResolverInterface
   {
       public function __construct(
           private SerializerInterface $serializer,
           private ValidatorInterface $validator
       ) {
       }
   
       /**
        * @param Request $request
        * @param ArgumentMetadata $argument
        * @return iterable
        *
        * @throws ApiValidationException
        * @throws ExceptionInterface
        */
       public function resolve(Request $request, ArgumentMetadata $argument): iterable
       {
           if ($argument->getType() != CreateOrderDto::class) {
               throw new BadRequestHttpException('Wrong request type');
           }
   
           $deserializedDto = $this->serializer->deserialize($request->getContent(), CreateOrderDto::class, 'json');
   
           $violationsList = $this->validator->validate($deserializedDto);
   
           if ($violationsList->count() > 0) {
               $violations = [];
               foreach ($violationsList as $violation) {
                   $violations[$violation->getPropertyPath()] = $violation->getMessage();
               }
   
               throw new ApiValidationException($violations);
           }
   
           $deserializedDto->_source = $request->getRequestUri();
   
           return [$deserializedDto];
       }
   }
   ```
3. Исправляем сигнатуру метода `__invoke` контроллера `App\Infrastructure\Delivery\Api\CreateOrder\v1\CreateOrderApiController`
   ```php
   public function __invoke(#[MapRequestPayload(resolver: CreateOrderValueResolver::class)] CreateOrderDto $createOrderDto): JsonResponse
   ```
4. Выполняем запрос из Postman-коллекции `OK/OK /api/create-order/v1`. В отладчике видим, что поле `$_source` заполнено адресом ендпоинта

## Добавляем централизованную обработку исключений
1. Создаём интерфейс `App\Domain\Response\ApiResponseInterface`
   ```php
   <?php
   
   namespace App\Domain\Response;
   
   interface ApiResponseInterface
   {
       public function getResultCode(): int;
   }
   ```
2. Создаём класс `App\Domain\Response\AbstractResponse`
   ```php
   <?php
   
   namespace App\Domain\Response;
   
   class AbstractResponse
   {
       public function __construct(
           public readonly bool $success,
           public readonly int $resultCode,
           public readonly ?string $message,
           public readonly mixed $data,
       ) {
       }
   }
   ```
3. Создаём класс `App\Domain\Response\AbstractResponse\ErrorResponse`
   ```php
   <?php
   
   namespace App\Domain\Response;
   
   class ErrorResponse extends AbstractResponse implements ApiResponseInterface
   {
       public function __construct(?string $message, int $resultCode)
       {
           parent::__construct(
               success: false,
               resultCode: $resultCode,
               message: $message,
               data: null
           );
       }
   
       public function getResultCode(): int
       {
           return $this->resultCode;
       }
   }
   ```
4. Создаём интерфейс `App\Domain\Exception\ApiExceptionInterface`
   ```php
   <?php
   
   namespace App\Domain\Exception;
   
   interface ApiExceptionInterface
   {
       public function getStatusCode(): int;
   
       public function getMessage(): string;
   }
   ```
5. Создаём класс `App\Domain\Exception\ApiValidationException`
   ```php
   <?php
   
   namespace App\Domain\Exception;
   
   use Symfony\Component\HttpFoundation\Response;
   
   class ApiValidationException extends \Exception implements ApiExceptionInterface
   {
       public function __construct(array $violations)
       {
           $message = implode('. ', $violations);
           parent::__construct($message, Response::HTTP_BAD_REQUEST);
       }
   
       public function getStatusCode(): int
       {
           return Response::HTTP_BAD_REQUEST;
       }
   }
   ```
6. В секцию `services` файла `config/services.yaml` добавляем наш listener
   ```yaml
   App\Domain\EventListener\KernelExceptionEventListener:
   tags:
     - { name: kernel.event_listener, event: kernel.exception }
   ```   
7. Выполняем любой запрос с ошибкой из коллекции Postman, видим, что все ответы теперь соответствуют нашему формату

## Добавляем централизованную обработку ответов

1. Создаём класс `App\Domain\Response\SuccessResponse`
   ```php
   <?php
   
   namespace App\Domain\Response;
   
   class SuccessResponse extends AbstractResponse implements ApiResponseInterface
   {
       public function __construct(mixed $data, ?string $message, int $resultCode)
       {
           parent::__construct(
               success: true,
               resultCode: $resultCode,
               message: $message,
               data: $data
           );
       }
   
       public function getResultCode(): int
       {
           return $this->resultCode;
       }
   }
   ```
2. Создаём класс `App\Domain\EventListener\KernelViewEventListener`
   ```php
   <?php
   
   namespace App\Domain\EventListener;
   
   use App\Domain\Response\ApiResponseInterface;
   use App\Domain\Response\SuccessResponse;
   use Symfony\Component\HttpFoundation\JsonResponse;
   use Symfony\Component\HttpFoundation\Response;
   use Symfony\Component\HttpKernel\Event\ViewEvent;
   use Symfony\Component\Serializer\Encoder\JsonEncoder;
   use Symfony\Component\Serializer\Exception\ExceptionInterface;
   use Symfony\Component\Serializer\SerializerInterface;
   
   final readonly class KernelViewEventListener
   {
       public function __construct(private SerializerInterface $serializer)
       {
       }
   
       /**
        * @param ViewEvent $event
        * @return void
        *
        * @throws ExceptionInterface
        */
       public function onKernelView(ViewEvent $event): void
       {
           $controllerResult = $event->getControllerResult();
   
           $response = $this->resolveResponse($controllerResult);
   
           $jsonResponse = new JsonResponse(
               data: $this->serializer->serialize($controllerResult, JsonEncoder::FORMAT),
               status: $response->getResultCode(),
               json: true
           );
   
           $event->setResponse($jsonResponse);
       }
   
       private function resolveResponse(mixed $controllerResult): ApiResponseInterface
       {
           if ($controllerResult instanceof ApiResponseInterface) {
               return $controllerResult;
           }
   
           return new SuccessResponse(
               data: $controllerResult,
               message: null,
               resultCode: Response::HTTP_OK
           );
       }
   }
   ```
3. В секцию `services` файла `config/services.yaml` добавляем наш listener
   ```yaml
   App\Domain\EventListener\KernelViewEventListener:
   tags:
     - { name: kernel.event_listener, event: kernel.exception }
   ```
4. Исправляем контроллер `App\Infrastructure\Delivery\Api\CreateOrder\v1\CreateOrderApiController`:
   ```php
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
   ```
5. Выполняем успешеный запрос из коллекции Postman, видим, что ответ теперь соответствуют нашему формату

## Выносим логику в отдельный сервис и применяем Symfony Message для CQRS
