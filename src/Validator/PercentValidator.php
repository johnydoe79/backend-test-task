<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PercentValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var Percent $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        // Здесь добавьте логику для проверки корректности налогового номера
        // Пример простой проверки
        if ($value > 100) {
            // Если размер скидки рпевышает 100 процентов, добавляем ошибку
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
