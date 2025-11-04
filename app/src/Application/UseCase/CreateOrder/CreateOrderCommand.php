<?php

namespace App\Application\UseCase\CreateOrder;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderCommand
{
    public function __construct(
        public ?string $_source,

        #[Assert\Positive(message: 'Client Id must be greater than 0')]
        public int $clientId,

        #[Assert\NotBlank(message: 'Order content must contain at least one order item')]
        public array $orderContent
    ) {
    }
}
