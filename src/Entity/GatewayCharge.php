<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\GatewayChargeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GatewayChargeRepository::class)]
class GatewayCharge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The money to be charged by the Gateway at checkout.
     */
    #[Assert\NotBlank()]
    #[ORM\Embedded(class: Money::class)]
    private ?Money $money = null;

    /**
     * The Accounting that will receive the Transaction of this GatewayCharge after a successful checkout.
     */
    #[Assert\NotBlank()]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $target = null;

    /**
     * Custom data to be passed to the Gateway.\
     * E.g: billing period, product name, custom metadata, etc.
     */
    #[ORM\Column(nullable: true)]
    private ?array $extradata = null;

    #[API\ApiProperty(writable: false, readable: false)]
    #[ORM\ManyToOne(inversedBy: 'charges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GatewayCheckout $checkout = null;

    /**
     * The generated Transaction inside the platform for this GatewayCharge.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(inversedBy: 'gatewayCharge', cascade: ['persist', 'remove'])]
    private ?Transaction $transaction = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTarget(): ?Accounting
    {
        return $this->target;
    }

    public function setTarget(?Accounting $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getExtradata(): ?array
    {
        return $this->extradata;
    }

    public function setExtradata(?array $extradata): static
    {
        $this->extradata = $extradata;

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

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }
}
