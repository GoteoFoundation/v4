<?php

namespace App\Validator;

use App\Library\Economy\Payment\GatewayLocator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\ChoiceValidator;

class GatewayNameValidator extends ChoiceValidator
{
    public function __construct(
        private GatewayLocator $gateways,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        /* @var App\Validator\GatewayName $constraint */

        if ($value === null || $value === '') {
            return;
        }

        try {
            if ($this->gateways->getGateway($value)) {
                return;
            }
        } catch (\Exception $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ choices }}', $this->formatValues($constraint->choices))
                ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                ->addViolation();
        }
    }
}
