<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Entity\Accounting\Accounting;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Interface\UserOwnedInterface;
use App\Entity\Trait\TimestampableCreationEntity;
use App\Entity\Trait\TimestampableUpdationEntity;
use App\Filter\OrderedLikeFilter;
use App\Filter\UserQueryFilter;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Users represent people who interact with the platform.\
 * \
 * Users are the usual issuers of funding, however an User's Accounting can still be a Transaction recipient.
 * This allows to keep an User's "wallet", witholding their non-raised fundings into their Accounting.
 */
#[Gedmo\Loggable()]
#[API\GetCollection()]
#[API\Post(validationContext: ['groups' => ['default', 'postValidation']])]
#[API\Get()]
#[API\Put(security: 'is_granted("USER_EDIT", object)')]
#[API\Delete(security: 'is_granted("USER_EDIT", object)')]
#[API\Patch(security: 'is_granted("USER_EDIT", object)')]
#[API\ApiFilter(filterClass: UserQueryFilter::class, properties: ['query'])]
#[API\ApiFilter(filterClass: OrderedLikeFilter::class, properties: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'This usernames already exists.')]
#[UniqueEntity(fields: ['email'], message: 'This email address is already registered.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index(fields: ['migratedId'])]
class User implements UserInterface, UserOwnedInterface, PasswordAuthenticatedUserInterface, AccountingOwnerInterface
{
    use TimestampableCreationEntity;
    use TimestampableUpdationEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Human readable, non white space, unique string.
     */
    #[Gedmo\Versioned]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 4, max: 30)]
    #[Assert\Regex('/^[a-z0-9_]+$/')]
    #[ORM\Column(length: 255)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles. Admin only property.
     */
    #[ORM\Column]
    #[API\ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private array $roles = [];

    /**
     * @var string The user password
     */
    #[API\ApiProperty(writable: false, readable: false)]
    #[ORM\Column]
    private ?string $password = null;

    /**
     * Plain-text, will be hashed by the platform.
     */
    #[Assert\NotBlank(['groups' => ['postValidation']])]
    #[Assert\Length(min: 12)]
    #[API\ApiProperty(writable: true, readable: false, required: true)]
    #[SerializedName('password')]
    private ?string $plainPassword = null;

    #[Gedmo\Versioned]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /**
     * Has this User confirmed their email address?
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private ?bool $emailConfirmed = null;

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(cascade: ['persist'])]
    private ?Accounting $accounting = null;

    /**
     * The UserTokens owned by this User. Owner only property.
     */
    #[API\ApiProperty(writable: false, security: 'is_granted("USER_OWNED", object)')]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: UserToken::class, orphanRemoval: true)]
    private Collection $tokens;

    /**
     * A flag determined by the platform for Users who are known to be active.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private ?bool $active = null;

    /**
     * Path to the Users's avatar image.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    /**
     * Conventional name of the person owning this User.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    /**
     * User was migrated from Goteo v3 platform.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private ?bool $migrated = null;

    /**
     * The previous id of this User in the Goteo v3 platform.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $migratedId = null;

    /**
     * The projects owned by this User.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Project::class)]
    private Collection $projects;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserPersonal $personalData = null;

    public function __construct()
    {
        $this->emailConfirmed = false;
        $this->active = false;
        $this->migrated = false;

        $this->tokens = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = strtolower($username);

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[API\ApiProperty(readable: false)]
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getOwner(): ?User
    {
        return $this;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->getUserIdentifier() === $user->getUserIdentifier();
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRoles(array $roles): bool
    {
        return count(array_intersect($this->getRoles(), $roles)) > 0;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isEmailConfirmed(): ?bool
    {
        return $this->emailConfirmed;
    }

    public function setEmailConfirmed(bool $emailConfirmed): static
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    public function getAccounting(): ?Accounting
    {
        return $this->accounting;
    }

    public function setAccounting(Accounting $accounting): static
    {
        $this->accounting = $accounting;

        return $this;
    }

    /**
     * @return Collection<int, UserToken>
     */
    public function getTokens(): Collection
    {
        return $this->tokens;
    }

    public function addToken(UserToken $token): static
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens->add($token);
            $token->setOwner($this);
        }

        return $this;
    }

    public function removeToken(UserToken $token): static
    {
        if ($this->tokens->removeElement($token)) {
            // set the owning side to null (unless already changed)
            if ($token->getOwner() === $this) {
                $token->setOwner(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isMigrated(): ?bool
    {
        return $this->migrated;
    }

    public function setMigrated(bool $migrated): static
    {
        $this->migrated = $migrated;

        return $this;
    }

    public function getMigratedId(): ?string
    {
        return $this->migratedId;
    }

    public function setMigratedId(?string $migratedId): static
    {
        $this->migratedId = $migratedId;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setOwner($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getOwner() === $this) {
                $project->setOwner(null);
            }
        }

        return $this;
    }

    public function getPersonalData(): ?UserPersonal
    {
        return $this->personalData;
    }

    public function setPersonalData(UserPersonal $personalData): static
    {
        // set the owning side of the relation if necessary
        if ($personalData->getUser() !== $this) {
            $personalData->setUser($this);
        }

        $this->personalData = $personalData;

        return $this;
    }
}
