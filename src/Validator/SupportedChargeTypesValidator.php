<?php

namespace App\Validator;

use App\Gateway\GatewayLocator;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SupportedChargeTypesValidator extends ConstraintValidator
{
    public function __construct(
        private GatewayLocator $gatewayLocator,
    ) {}

    /**
     * @param Collection<int, \App\Entity\Gateway\Charge> $value
     * @param SupportedChargeTypes $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!$value instanceof Collection) {
            return;
        }

        $checkout = $value->toArray()[0]->getCheckout();
        $gateway = $this->gatewayLocator->getForCheckout($checkout);
        $supportedTypes = $gateway->getSupportedChargeTypes();

        foreach ($value as $charge) {
            if (!\in_array($charge->getType(), $supportedTypes)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $charge->getType()->value)
                    ->setParameter('{{ gateway }}', $gateway->getName())
                    ->addViolation();
            }
        }
    }
}
