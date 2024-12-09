<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\State\ApiResourceStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Projects describe a User-owned, community-led event that is to be discovered, developed and funded by the community.
 */
#[API\ApiResource(
    shortName: 'Project',
    stateOptions: new Options(entityClass: Project::class),
    provider: ApiResourceStateProvider::class,
)]
#[API\GetCollection()]
#[API\Post(security: 'is_granted("ROLE_USER")')]
#[API\Get()]
#[API\Patch(security: 'is_granted("PROJECT_EDIT")')]
#[API\Delete(security: 'is_granted("PROJECT_EDIT")')]
class ProjectApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The Accounting holding the funds raised by this Project.
     */
    #[API\ApiProperty(writable: false)]
    public AccountingApiResource $accounting;

    /**
     * Main title for the Project.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'partial')]
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("PROJECT_EDIT")')]
    #[Assert\NotBlank()]
    public string $title;

    /**
     * The status of a Project represents how far it is in it's life-cycle.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("PROJECT_EDIT")')]
    public ProjectStatus $status = ProjectStatus::InEditing;
}
