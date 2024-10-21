<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\GatewayChargeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCharge represents a monetary payment that can be done by an issuer at checkout with the Gateway.
 */
#[API\ApiResource()]
#[ORM\Entity(repositoryClass: GatewayChargeRepository::class)]
class GatewayCharge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The type represents the kind of payment for the charged money.
     */
    #[Assert\NotBlank()]
    #[ORM\Column()]
    private ?GatewayChargeType $type = null;

    /**
     * A short, descriptive text for this charge operation.
     */
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * Detailed message about this charge operation.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * The Accounting receiving the consequent Transaction for this GatewayCharge.
     */
    #[Assert\NotBlank()]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $target = null;

    /**
     * The charged monetary sum.
     */
    #[Assert\NotBlank()]
    #[ORM\Embedded(Money::class)]
    private ?Money $money = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?GatewayChargeType
    {
        return $this->type;
    }

    public function setType(GatewayChargeType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTarget(): ?Accounting
    {
        return $this->target;
    }

    public function setTarget(?Accounting $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getMoney(): ?Money
    {
        return $this->money;
    }

    public function setMoney(Money $money): static
    {
        $this->money = $money;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
