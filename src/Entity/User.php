<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Users represent people who interact with the platform.
 */
#[API\GetCollection()]
#[API\Post()]
#[API\Get()]
#[API\Put(security: 'is_granted("AUTH_USER_EDIT")')]
#[API\Delete(security: 'is_granted("AUTH_USER_EDIT")')]
#[API\Patch(security: 'is_granted("AUTH_USER_EDIT")')]
#[UniqueEntity(fields: ['username'], message: 'This usernames already exists.')]
#[UniqueEntity(fields: ['email'], message: 'This email address is already registered.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Human readable, non white space, unique string.
     */
    #[Assert\NotBlank()]
    #[Assert\Length(min: 4, max: 30)]
    #[Assert\Regex('/^[a-z0-9_-]+$/')]
    #[ORM\Column(length: 30, unique: true)]
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
    #[Assert\NotBlank()]
    #[Assert\Length(min: 12)]
    #[API\ApiProperty(writable: true, readable: false)]
    #[SerializedName('password')]
    private ?string $plainPassword = null;

    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    /**
     * The AccessTokens owned by this user. Owner only property.
     */
    #[API\ApiProperty(writable: false, readableLink: true, security: 'is_granted("AUTH_OWNER")')]
    #[ORM\OneToMany(mappedBy: 'ownedBy', targetEntity: AccessToken::class, orphanRemoval: true)]
    private Collection $accessTokens;

    public function __construct()
    {
        $this->account = new Account;
        $this->accessTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
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

    public function getPlainPassword(): string
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

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Collection<int, AccessToken>
     */
    public function getAccessTokens(): Collection
    {
        return $this->accessTokens;
    }

    public function addAccessToken(AccessToken $accessToken): static
    {
        if (!$this->accessTokens->contains($accessToken)) {
            $this->accessTokens->add($accessToken);
            $accessToken->setOwnedBy($this);
        }

        return $this;
    }

    public function removeAccessToken(AccessToken $accessToken): static
    {
        if ($this->accessTokens->removeElement($accessToken)) {
            // set the owning side to null (unless already changed)
            if ($accessToken->getOwnedBy() === $this) {
                $accessToken->setOwnedBy(null);
            }
        }

        return $this;
    }
}
