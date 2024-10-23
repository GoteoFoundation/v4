<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that a GatewayCheckout has GatewayCharges with charge types supported by the given Gateway.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class SupportedChargeTypes extends Constraint
{
    public string $message = 'The charge type "{{ value }}" is not supported by the "{{ gateway }}" Gateway.';
}
