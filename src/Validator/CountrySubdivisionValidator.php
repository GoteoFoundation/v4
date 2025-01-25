<?php

namespace App\Validator;

use Sokil\IsoCodes\IsoCodesFactory;
use Sokil\IsoCodes\TranslationDriver\DummyDriver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CountrySubdivisionValidator extends ConstraintValidator
{
    /**
     * @param CountrySubdivion $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (self::validateISO3166_2($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation()
        ;
    }

    public static function validateISO3166_2(string $iso3166_2): bool
    {
        $pieces = \explode('-', $iso3166_2);

        if (\count($pieces) < 2) {
            return false;
        }

        $isoCodes = new IsoCodesFactory(null, new DummyDriver());

        return (bool) $isoCodes->getSubdivisions()->getByCode($iso3166_2);
    }
}
