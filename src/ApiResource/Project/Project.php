<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\Accounting;
use App\Entity\Project as Entity;
use App\Entity\Project\ProjectStatus;
use App\Entity\User;
use App\State\ApiResourceStateProcessor;
use App\State\Project\ProjectStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Project represents a User-led crowdfunding event that is to be discovered and supported by the wider community.
 */
#[API\ApiResource(
    stateOptions: new Options(entityClass: Entity\Project::class),
    provider: ProjectStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
#[API\GetCollection()]
#[API\Post(security: 'is_granted("ROLE_USER")')]
#[API\Get()]
#[API\Patch(security: 'is_granted("PROJECT_EDIT")')]
#[API\Delete(security: 'is_granted("PROJECT_EDIT")')]
class Project
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The User who launched this Project.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    #[API\ApiProperty(writable: false)]
    public User $owner;

    /**
     * The Accounting processing the funds involved in this Project.
     */
    #[API\ApiProperty(writable: false)]
    public Accounting $accounting;

    /**
     * Main title for this Project.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank()]
    public string $title;

    /**
     * The current status of this Project.\
     * Admin-only property.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    public ProjectStatus $status = ProjectStatus::InEditing;

    /** @var array<int, Reward> */
    public array $rewards = [];
}
