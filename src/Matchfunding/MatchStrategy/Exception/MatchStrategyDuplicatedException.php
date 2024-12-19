<?php

namespace App\Matchfunding\MatchStrategy\Exception;

class MatchStrategyDuplicatedException extends \Exception
{
    public const DUPLICATED_NAME = "Duplicate MatchStrategy name '%s' by class '%s', value already in use by class '%s'";

    public function __construct(
        string $duplicatedName,
        string $duplicatedClass,
        string $strategyClass,
        string $message = self::DUPLICATED_NAME
    ) {

        parent::__construct(\sprintf(
            $message,
            $duplicatedName,
            $duplicatedClass,
            $strategyClass
        ));
    }
}
