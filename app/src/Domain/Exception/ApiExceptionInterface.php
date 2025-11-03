<?php

namespace App\Domain\Exception;

interface ApiExceptionInterface
{
    public function getStatusCode(): int;

    public function getMessage(): string;
}
