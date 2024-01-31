<?php

namespace App\Validator;

use App\Library\Economy\Payment\GatewayLocator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;

class GatewayNameValidator extends ChoiceValidator
{
    public function __construct(
        private GatewayLocator $gateways
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var App\Validator\GatewayName $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if ($this->gateways->getGateway($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
