<?php

namespace App\Validator;

use App\Library\Economy\Payment\GatewayLocator;
use Symfony\Component\Validator\Constraints\Choice;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class GatewayName extends Choice
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'The value "{{ value }}" is not valid.';

    public function __construct()
    {
        parent::__construct(options: GatewayLocator::getNamesStatic());
    }
}
