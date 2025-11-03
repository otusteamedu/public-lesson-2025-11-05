<?php

namespace App\Domain\Response;

class ErrorResponse extends AbstractResponse implements ApiResponseInterface
{
    public function __construct(?string $message, int $resultCode)
    {
        parent::__construct(false, $resultCode, $message, null);
    }
}