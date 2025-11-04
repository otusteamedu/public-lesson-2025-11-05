<?php

namespace App\Domain\Validation;

use App\Application\UseCase\CreateOrder\CreateOrderCommand;
use App\Infrastructure\Persistence\Doctrine\Client\ClientEntityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsClientExistsConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly ClientEntityRepository $clientEntityRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsClientExistsConstraint) {
            throw new UnexpectedTypeException($constraint, IsClientExistsConstraint::class);
        }

        if (!$value instanceof CreateOrderCommand) {
            throw new UnexpectedTypeException($constraint, CreateOrderCommand::class);
        }

        $client = $this->clientEntityRepository->find($value->clientId);
        if (empty($client)) {
            $this->context->buildViolation('Client not found')->addViolation();
        }
    }
}