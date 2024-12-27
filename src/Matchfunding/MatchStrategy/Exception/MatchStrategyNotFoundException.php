<?php

namespace App\Matchfunding\MatchStrategy\Exception;

class MatchStrategyNotFoundException extends \Exception
{
    public const MISSING_NAME = "Could not find a MatchStrategy by the name '%s', value does not exist";

    public function __construct(
        string $name,
        string $message = self::MISSING_NAME,
        ...$params
    ) {
        parent::__construct(\sprintf(
            $message,
            ...[$name, ...$params]
        ));
    }
}
