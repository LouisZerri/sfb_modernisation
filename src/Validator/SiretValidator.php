<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class SiretValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Siret) {
            throw new UnexpectedValueException($constraint, Siret::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $digits = preg_replace('/\s+/', '', $value);

        if (null === $digits || 1 !== preg_match('/^\d{14}$/', $digits) || !$this->isLuhnValid($digits)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function isLuhnValid(string $number): bool
    {
        $sum = 0;
        $length = \strlen($number);

        for ($i = 0; $i < $length; ++$i) {
            $digit = (int) $number[$length - 1 - $i];

            if (1 === $i % 2) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return 0 === $sum % 10;
    }
}
