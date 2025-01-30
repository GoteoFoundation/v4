<?php

namespace App\ApiResource\User;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\Dto\UserSignupDto;
use App\Entity\User\User;
use App\Filter\OrderedLikeFilter;
use App\Filter\UserQueryFilter;
use App\State\ApiResourceStateProvider;
use App\State\User\UserSignupProcessor;
use App\State\User\UserStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Users represent people who interact with the platform.
 */
#[API\ApiResource(
    shortName: 'User',
    stateOptions: new Options(entityClass: User::class),
    provider: ApiResourceStateProvider::class,
    processor: UserStateProcessor::class,
)]
#[API\GetCollection()]
#[API\Post(input: UserSignupDto::class, processor: UserSignupProcessor::class)]
#[API\Get()]
#[API\Patch(security: 'is_granted("USER_EDIT", object)')]
#[API\Delete(security: 'is_granted("USER_EDIT", object)')]
#[API\ApiFilter(filterClass: UserQueryFilter::class, properties: ['query'])]
class UserApiResource
{
    #[API\ApiProperty(writable: false, identifier: true)]
    public int $id;

    #[Assert\NotBlank()]
    #[Assert\Email()]
    public string $email;

    #[API\ApiProperty(writable: false)]
    public bool $emailConfirmed;

    /**
     * A unique, non white space, byte-safe string identifier for this User.
     */
    #[API\ApiFilter(filterClass: OrderedLikeFilter::class)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 4, max: 30)]
    #[Assert\Regex('/^[a-z0-9_]+$/')]
    public string $username;

    /**
     * Display name chosen by the User.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'partial')]
    public string $name;

    /**
     * A list of the roles assigned to this User. Admin scopped property.
     *
     * @var array<int, string>
     */
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    public array $roles;

    /**
     * The Accounting for this User monetary movements.
     */
    #[API\ApiProperty(writable: false)]
    public AccountingApiResource $accounting;

    /**
     * The Projects that are owned by this User.
     *
     * @var array<int, \App\ApiResource\Project\ProjectApiResource>
     */
    #[API\ApiProperty(writable: false)]
    public array $projects;
}
