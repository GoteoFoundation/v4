<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Metadata as API;
use App\State\Matchfunding\MatchStrategyStateProvider;

/**
 * A MatchStrategy is a predefined code implementation for matching funds in Transactions under a MatchCall.
 * MatchStrategies can be chosen by the managers in a MatchCall and their behaviour fine-tuned.
 */
#[API\ApiResource(
    shortName: 'MatchStrategy',
    provider: MatchStrategyStateProvider::class
)]
class MatchStrategyApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public string $name;
}
