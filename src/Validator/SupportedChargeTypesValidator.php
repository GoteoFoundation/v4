<?php

namespace App\Validator;

use App\Library\Economy\Payment\GatewayLocator;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SupportedChargeTypesValidator extends ConstraintValidator
{
    public function __construct(
        private GatewayLocator $gatewayLocator,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!$value instanceof Collection) {
            return;
        }

        /** @var Collection<int, \App\Entity\GatewayCharge> */
        $charges = $value;

        $checkout = $charges->toArray()[0]->getCheckout();
        $gateway = $this->gatewayLocator->getGatewayOf($checkout);

        foreach ($charges as $charge) {
            if (!\in_array(
                $charge->getType(),
                $gateway->getSupportedChargeTypes()
            )) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $charge->getType()->value)
                    ->setParameter('{{ gateway }}', $gateway->getName())
                    ->addViolation();
            }
        }
    }
}
