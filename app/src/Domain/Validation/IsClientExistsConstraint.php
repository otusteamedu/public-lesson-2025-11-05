<?php

namespace App\Domain\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsClientExistsConstraint extends Constraint
{
    public function __construct(
        array $groups = null,
        $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}