<?php

namespace App\Gateway\Exception;

class DuplicateGatewayException extends \Exception
{
    public const MESSAGE_DUPLICATE = 'Duplicate Gateway name \'%s\' from class %s, name is already in use by class %s';

    public function __construct(
        string $duplicateName,
        string $duplicateClass,
        string $existingClass,
    ) {
        parent::__construct(sprintf(
            self::MESSAGE_DUPLICATE,
            $duplicateName,
            $duplicateClass,
            $existingClass
        ));
    }
}
