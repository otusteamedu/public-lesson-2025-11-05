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
