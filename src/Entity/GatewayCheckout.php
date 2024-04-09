<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Dto\GatewayCheckoutUpdateDto;
use App\Repository\GatewayCheckoutRepository;
use App\State\GatewayCheckoutProcessor;
use App\State\GatewayCheckoutUpdateProcessor;
use App\Validator\GatewayName;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCheckout bundles the data to perform a charge operation at a Gateway.
 * Use it in order to create Transactions and have the transferred money be backed by a Gateway's payment processing.
 */
#[API\GetCollection()]
#[API\Post(processor: GatewayCheckoutProcessor::class)]
#[API\Get()]
#[API\Patch(input: GatewayCheckoutUpdateDto::class, processor: GatewayCheckoutUpdateProcessor::class)]
#[ORM\Entity(repositoryClass: GatewayCheckoutRepository::class)]
class GatewayCheckout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The Accounting that will issue the Transactions of the GatewayCharges after a successful checkout.
     */
    #[Assert\NotBlank()]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $origin = null;

    /**
     * The charges to be made by the Gateway at checkout.\
     * Each GatewayCharge will generate a separate Transaction after a successful checkout.
     */
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1)]
    #[ORM\OneToMany(
        mappedBy: 'checkout',
        targetEntity: GatewayCharge::class,
        cascade: ['persist']
    )]
    private Collection $charges;

    /**
     * The name of the Gateway implementation to checkout with.
     */
    #[GatewayName]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $gateway = null;

    /**
     * An external identifier provided by the Gateway for the payment.\
     * Required when a GatewayCheckout is completed.
     */
    #[ORM\Column(length: 255)]
    private ?string $gatewayReference = null;

    public function __construct()
    {
        $this->charges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrigin(): ?Accounting
    {
        return $this->origin;
    }

    public function setOrigin(?Accounting $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return Collection<int, GatewayCharge>
     */
    public function getCharges(): Collection
    {
        return $this->charges;
    }

    public function addCharge(GatewayCharge $charge): static
    {
        if (!$this->charges->contains($charge)) {
            $this->charges->add($charge);
            $charge->setCheckout($this);
        }

        return $this;
    }

    public function removeCharge(GatewayCharge $charge): static
    {
        if ($this->charges->removeElement($charge)) {
            // set the owning side to null (unless already changed)
            if ($charge->getCheckout() === $this) {
                $charge->setCheckout(null);
            }
        }

        return $this;
    }

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    public function setGateway(string $gateway): static
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function getGatewayReference(): ?string
    {
        return $this->gatewayReference;
    }

    public function setGatewayReference(string $gatewayReference): static
    {
        $this->gatewayReference = $gatewayReference;

        return $this;
    }
}
