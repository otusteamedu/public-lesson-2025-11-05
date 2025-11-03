<?php

namespace App\Domain\Response;

interface ApiResponseInterface
{
    public function getResultCode(): int;
}