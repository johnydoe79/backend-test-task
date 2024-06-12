<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TaxNumberValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var TaxNumber $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        //Можно хранить паттерны в БД
        $countryCode = substr($value, 0, 2);
        $regexPattern = match ($countryCode) {
            'DE' => '/^'.$countryCode.'\d{9}$/',
            'IT' => '/^'.$countryCode.'\d{11}$/',
            'GR' => '/^'.$countryCode.'\d{9}$/',
            'FR' => '/^'.$countryCode.'\p{L}{2}\d{9}$/u',
            default => [],
        };

        if (!preg_match($regexPattern, $value)) {
            // Если налоговый номер не соответствует шаблону, добавляем ошибку
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
