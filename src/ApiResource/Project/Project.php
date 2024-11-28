<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\Accounting;
use App\Entity\Project as Entity;
use App\Entity\Project\ProjectStatus;
use App\Entity\User;
use App\State\Project\ProjectStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Project represents a User-led crowdfunding event that is to be discovered and supported by the wider community.
 */
#[API\ApiResource(
    stateOptions: new Options(entityClass: Entity\Project::class),
    provider: ProjectStateProvider::class
)]
class Project
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The User who launched this Project.
     */
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
    #[Assert\NotBlank()]
    public string $title;

    /**
     * The current status of this Project.\
     * Admin-only property.
     */
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    public ProjectStatus $status = ProjectStatus::InEditing;
}
