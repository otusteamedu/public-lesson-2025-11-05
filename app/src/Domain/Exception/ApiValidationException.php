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