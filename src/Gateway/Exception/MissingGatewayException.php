<?php

namespace App\Gateway\Exception;

class MissingGatewayException extends \Exception
{
    public const MESSAGE_NOT_FOUND = 'Could not match \'%s\' to the name of any available Gateway';

    public function __construct(string $missingName)
    {
        parent::__construct(\sprintf(self::MESSAGE_NOT_FOUND, $missingName));
    }
}
