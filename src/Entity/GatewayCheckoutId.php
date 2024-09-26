<?php

namespace App\Entity;

use App\Repository\GatewayCheckoutIdRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GatewayCheckoutIdRepository::class)]
class GatewayCheckoutId
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The ID given by the Gateway.
     */
    #[ORM\Column(length: 255)]
    private ?string $value = null;

    /**
     * A descriptive title for the ID type.
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'gatewayIds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GatewayCheckout $checkout = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

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

    public function getCheckout(): ?GatewayCheckout
    {
        return $this->checkout;
    }

    public function setCheckout(?GatewayCheckout $checkout): static
    {
        $this->checkout = $checkout;

        return $this;
    }
}
