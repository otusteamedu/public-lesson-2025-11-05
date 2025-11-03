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