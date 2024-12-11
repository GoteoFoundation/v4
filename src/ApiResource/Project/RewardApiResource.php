<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\Money;
use App\Entity\Project\Reward;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
use AutoMapper\Attribute\MapTo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A ProjectReward is something the Project owner wishes to give in exchange for contributions to their Project.
 */
#[API\ApiResource(
    shortName: 'ProjectReward',
    stateOptions: new Options(entityClass: Reward::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
class RewardApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The project which gives this reward.
     */
    #[Assert\NotBlank()]
    public ProjectApiResource $project;

    /**
     * A short, descriptive title for this reward.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Detailed information about this reward.
     */
    #[MapTo(if: 'source.description != null')]
    public ?string $description = null;

    /**
     * The minimal monetary sum to be able to claim this reward.
     */
    #[Assert\NotBlank()]
    public Money $money;

    /**
     * Rewards might be finite, i.e: has a limited amount of existing unitsTotal.
     */
    #[Assert\NotNull()]
    #[Assert\Type('bool')]
    public bool $hasUnits;

    /**
     * For finite rewards, the total amount of existing unitsTotal.
     */
    #[Assert\When(
        'this.hasUnits == true',
        constraints: [new Assert\Positive()]
    )]
    public int $unitsTotal = 0;

    /**
     * For finite rewards, the currently available amount of unitsTotal that can be claimed.
     */
    #[API\ApiProperty(writable: false)]
    public int $unitsAvailable = 0;
}
