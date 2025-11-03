<?php

namespace App\Infrastructure\Delivery\Api\CreateOrder\v1\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrderDto
{
    public function __construct(
        public ?string $_source,

        #[Assert\Positive(message: 'Client Id must be greater than 0')]
        public int $clientId,

        #[Assert\NotBlank(message: 'Order content must containt at least one order item')]
        public array $orderContent
    ) {
    }
}