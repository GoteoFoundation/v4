<?php

namespace App\Matchfunding\MatchStrategy;

use App\Entity\Matchfunding\MatchCall;
use App\Matchfunding\MatchStrategy\Exception\MatchStrategyDuplicatedException;
use App\Matchfunding\MatchStrategy\Exception\MatchStrategyNotFoundException;

class MatchStrategyLocator
{
    /** @var array<string, MatchStrategyInterface> */
    private array $strategiesByName = [];

    public function __construct(
        iterable $strategies,
    ) {
        foreach ($strategies as $strategy) {
            $strategyName = $strategy::getName();

            if (\array_key_exists($strategyName, $this->strategiesByName)) {
                throw new MatchStrategyDuplicatedException(
                    $strategyName,
                    $strategy::class,
                    $this->strategiesByName[$strategyName]::class
                );
            }

            $this->strategiesByName[$strategyName] = $strategy;
        }
    }

    public function get(string $strategyName): ?MatchStrategyInterface
    {
        if (!array_key_exists($strategyName, $this->strategiesByName)) {
            throw new MatchStrategyNotFoundException($strategyName);
        }

        return $this->strategiesByName[$strategyName];
    }

    public function getForCall(MatchCall $call): ?MatchStrategyInterface
    {
        return $this->get($call->getStrategyName());
    }
}
