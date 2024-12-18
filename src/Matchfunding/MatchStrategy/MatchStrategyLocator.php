<?php

namespace App\Matchfunding\MatchStrategy;

class MatchStrategyLocator
{
    /** @var array<string, MatchStrategyInterface> */
    private array $strategiesByName;

    public function __construct(
        iterable $strategies
    ) {
        $strategies = \iterator_to_array($strategies);

        foreach ($strategies as $strategy) {
            $strategyName = $strategy::getName();

            if (\array_key_exists($strategyName, $this->strategiesByName)) {
                throw new \Exception(\sprintf(
                    "Duplicate strategy name '%s' by '%s', value already in use by '%s'",
                    $strategyName,
                    $strategy::class,
                    $this->strategiesByName[$strategyName]::class
                ));
            }

            $this->strategiesByName[$strategyName] = $strategy;
        }
    }

    public function get(string $strategyName): ?MatchStrategyInterface
    {
        if (!array_key_exists($strategyName, $this->strategiesByName)) {
            return null;
        }

        return $this->strategiesByName[$strategyName];
    }
}
