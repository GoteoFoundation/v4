<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\User\UserApiResource;
use App\Entity\Project\RewardClaim;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A ProjectRewardClaim represents the will of an User who wishes to obtain one ProjectReward.
 */
#[API\ApiResource(
    shortName: 'ProjectRewardClaim',
    stateOptions: new Options(entityClass: RewardClaim::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
class RewardClaimApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The ProjectReward being claimed.
     */
    #[Assert\NotBlank()]
    public RewardApiResource $reward;

    /**
     * The User claiming the ProjectReward.
     */
    #[API\ApiProperty(writable: false)]
    public UserApiResource $owner;
}
