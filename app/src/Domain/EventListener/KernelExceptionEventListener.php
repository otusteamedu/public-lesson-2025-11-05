<?php

namespace App\Domain\EventListener;

use App\Domain\Exception\ApiExceptionInterface;
use App\Domain\Response\ErrorResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class KernelExceptionEventListener
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     *
     * @throws ExceptionInterface
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $errorResponse = $this->resolveResponse($exception);

        $jsonResponse = new JsonResponse(
            data: $this->serializer->serialize($errorResponse, JsonEncoder::FORMAT),
            status: $errorResponse->resultCode,
            json: true
        );

        $event->setResponse($jsonResponse);
    }

    private function resolveResponse(\Throwable $exception): ErrorResponse
    {
        return new ErrorResponse(
            message: $exception->getMessage(),
            resultCode: $this->resolveExceptionCode($exception)
        );
    }

    private function resolveExceptionCode(\Throwable $exception): int
    {
        if ($exception instanceof ApiExceptionInterface || $exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        $statusCode = $exception->getCode();

        if ($statusCode < Response::HTTP_CONTINUE || $statusCode > Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED) {
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $statusCode;
    }
}