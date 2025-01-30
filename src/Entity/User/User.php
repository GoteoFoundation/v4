<?php

namespace App\Entity\User;

use App\Entity\Accounting\Accounting;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Project\Project;
use App\Entity\Trait\MigratedEntity;
use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\TimestampedUpdationEntity;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\User\UserRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Users represent people who interact with the platform.\
 * \
 * Users are the usual issuers of funding, however an User's Accounting can still be a Transaction recipient.
 * This allows to keep an User's "wallet", withholding their non-raised fundings into their Accounting.
 */
#[Gedmo\Loggable()]
#[MapProvider(EntityMapProvider::class)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index(fields: ['migratedId'])]
#[UniqueEntity('email', message: 'This email address is already registered.')]
#[UniqueEntity('username', message: 'This usernames already exists.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, AccountingOwnerInterface
{
    use MigratedEntity;
    use TimestampedCreationEntity;
    use TimestampedUpdationEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Gedmo\Versioned]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    /**
     * Has this User confirmed their email address?
     */
    #[ORM\Column]
    private ?bool $emailConfirmed = null;

    /**
     * Human readable, non white space, unique string.
     */
    #[Gedmo\Versioned]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles. Admin only property.
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist'])]
    private ?Accounting $accounting = null;

    /**
     * The projects owned by this User.
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Project::class, cascade: ['persist'])]
    private Collection $projects;

    /**
     * The UserTokens owned by this User. Owner only property.
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: UserToken::class, orphanRemoval: true)]
    private Collection $tokens;

    /**
     * A flag determined by the platform for Users who are known to be active.
     */
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

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserPersonal $personalData = null;

    /**
     * @var string The user password
     */
    #[ORM\Column]
    private ?string $password = null;

    public function __construct()
    {
        $this->accounting = Accounting::of($this);

        $this->emailConfirmed = false;
        $this->active = false;

        $this->tokens = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
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

    public function getAccounting(): ?Accounting
    {
        return $this->accounting;
    }

    public function setAccounting(?Accounting $accounting): static
    {
        $this->accounting = $accounting;

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

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }
}
