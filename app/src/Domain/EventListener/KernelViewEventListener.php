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