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