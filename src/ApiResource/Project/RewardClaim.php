<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\Project as Entity;
use App\Entity\User;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A ProjectRewardClaim represents the will of an User who wishes to obtain one ProjectReward.
 */
#[API\ApiResource(
    shortName: 'ProjectRewardClaim',
    stateOptions: new Options(entityClass: Entity\Reward::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
class RewardClaim
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The ProjectReward being claimed.
     */
    #[Assert\NotBlank()]
    public Reward $reward;

    /**
     * The User claiming the ProjectReward.
     */
    #[API\ApiProperty(writable: false)]
    public User $owner;
}
