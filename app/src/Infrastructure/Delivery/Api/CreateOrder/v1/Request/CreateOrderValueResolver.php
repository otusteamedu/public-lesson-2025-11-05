<?php

namespace App\Infrastructure\Delivery\Api\CreateOrder\v1\Request;

use App\Application\UseCase\CreateOrder\CreateOrderCommand;
use App\Domain\Exception\ApiValidationException;
use App\Infrastructure\Persistence\Doctrine\Client\ClientEntityRepository;
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
        private ClientEntityRepository $clientEntityRepository,
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
        if ($argument->getType() != CreateOrderCommand::class) {
            throw new BadRequestHttpException('Wrong request type');
        }

        $createOrderCommand = $this->serializer->deserialize($request->getContent(), CreateOrderCommand::class, 'json');

        $violationsList = $this->validator->validate($createOrderCommand);

        if ($violationsList->count() > 0) {
            $violations = [];
            foreach ($violationsList as $violation) {
                $violations[$violation->getPropertyPath()] = $violation->getMessage();
            }

            throw new ApiValidationException($violations);
        }

        $client = $this->clientEntityRepository->find($createOrderCommand->clientId);

        if (empty($client)) {
            throw new BadRequestHttpException('Client not found');
        }

        $createOrderCommand->_source = $request->getRequestUri();

        return [$createOrderCommand];
    }
}