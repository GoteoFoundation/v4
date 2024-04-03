<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\GatewayCheckoutRepository;
use App\State\GatewayCheckoutProcessor;
use App\Validator\GatewayName;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GatewayCheckout
 */
#[API\GetCollection()]
#[API\Post(processor: GatewayCheckoutProcessor::class)]
#[API\Get()]
#[ORM\Entity(repositoryClass: GatewayCheckoutRepository::class)]
class GatewayCheckout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The name of the Gateway implementation to checkout with.
     */
    #[GatewayName]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $gateway = null;

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

    public function __construct()
    {
        $this->charges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
}
