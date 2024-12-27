<?php

namespace App\State\Matchfunding;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Matchfunding\MatchStrategyApiResource;
use App\Matchfunding\MatchStrategy\Exception\MatchStrategyNotFoundException;
use App\Matchfunding\MatchStrategy\MatchStrategyInterface;
use App\Matchfunding\MatchStrategy\MatchStrategyLocator;

class MatchStrategyStateProvider implements ProviderInterface
{
    public function __construct(
        private MatchStrategyLocator $matchStrategyLocator,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (\array_key_exists('name', $uriVariables)) {
            try {
                $strategy = $this->matchStrategyLocator->get($uriVariables['name']);

                return $this->getApiResource($strategy);
            } catch (MatchStrategyNotFoundException $e) {
                return null;
            }
        }

        $strategies = $this->matchStrategyLocator->getAll();

        $resources = [];
        foreach ($strategies as $strategy) {
            $resources[] = $this->getApiResource($strategy);
        }

        return $resources;
    }

    private function getApiResource(MatchStrategyInterface $strategy): MatchStrategyApiResource
    {
        $resource = new MatchStrategyApiResource();
        $resource->name = $strategy::getName();

        return $resource;
    }
}
