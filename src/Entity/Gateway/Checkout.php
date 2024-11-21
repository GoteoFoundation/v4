<?php

namespace App\Entity\Gateway;

use App\Entity\Accounting\Accounting;
use App\Entity\Trait\MigratedEntity;
use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\TimestampedUpdationEntity;
use App\Gateway\CheckoutStatus;
use App\Gateway\Link;
use App\Gateway\Tracking;
use App\Repository\Gateway\CheckoutRepository;
use App\Validator\SupportedChargeTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCheckout bundles the data to perform a payment operation with a Gateway.\
 * \
 * Once the Gateway validates the payment as successful the GatewayCheckout will be updated
 * and respective AccountingTransactions will be generated for each Charge.
 */
#[Gedmo\Loggable()]
#[ORM\Entity(repositoryClass: CheckoutRepository::class)]
#[ORM\Index(fields: ['migratedId'])]
class Checkout
{
    use MigratedEntity;
    use TimestampedCreationEntity;
    use TimestampedUpdationEntity;

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
     * The status of the checkout with the Gateway.
     */
    #[Gedmo\Versioned]
    #[ORM\Column()]
    private ?CheckoutStatus $status = null;

    /**
     * The GatewayCharges to be charged at checkout with the gateway.
     *
     * @var Collection<int, Charge>
     */
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1)]
    #[SupportedChargeTypes()]
    #[ORM\OneToMany(mappedBy: 'checkout', targetEntity: Charge::class, cascade: ['persist'])]
    private Collection $charges;

    /**
     * The name of the Gateway implementation to checkout with.
     */
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $gatewayName = null;

    /**
     * A list of URLs provided by the Gateway for this checkout.\
     * e.g: Fulfill payment, API resource address.
     *
     * @var Link[]
     */
    #[ORM\Column]
    private array $links = [];

    /**
     * A list of tracking codes provided by the Gateway for this checkout.\
     * e.g: Order ID, Payment Capture ID, Checkout Session Token.
     *
     * @var Tracking[]
     */
    #[ORM\Column]
    private array $trackings = [];

    public function __construct()
    {
        $this->status = CheckoutStatus::Pending;
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

    public function getStatus(): ?CheckoutStatus
    {
        return $this->status;
    }

    public function setStatus(CheckoutStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Charge>
     */
    public function getCharges(): Collection
    {
        return $this->charges;
    }

    /**
     * @param Collection<int, Charge> $charges
     */
    public function setCharges(Collection $charges): static
    {
        $this->charges = new ArrayCollection();

        foreach ($charges as $charge) {
            $this->charges->add($charge);
            $charge->setCheckout($this);
        }

        return $this;
    }

    public function addCharge(Charge $charge): static
    {
        if (!$this->charges->contains($charge)) {
            $this->charges->add($charge);
            $charge->setCheckout($this);
        }

        return $this;
    }

    public function removeCharge(Charge $charge): static
    {
        if ($this->charges->removeElement($charge)) {
            // set the owning side to null (unless already changed)
            if ($charge->getCheckout() === $this) {
                $charge->setCheckout(null);
            }
        }

        return $this;
    }

    public function getGatewayName(): ?string
    {
        return $this->gatewayName;
    }

    public function setGatewayName(string $gatewayName): static
    {
        $this->gatewayName = $gatewayName;

        return $this;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param Link[] $links
     */
    public function setLinks(array $links): static
    {
        $this->links = $links;

        return $this;
    }

    public function addLink(Link $link): static
    {
        $this->links = [...$this->links, $link];

        return $this;
    }

    public function removeLink(Link $link): static
    {
        $this->links = \array_filter(
            $this->links,
            function (Link $existingLink) use ($link) {
                return $existingLink->href !== $link->href;
            }
        );

        return $this;
    }

    /**
     * @return Tracking[]
     */
    public function getTrackings(): array
    {
        return $this->trackings;
    }

    /**
     * @param Tracking[] $trackings
     */
    public function setTrackings(array $trackings): static
    {
        $this->trackings = $trackings;

        return $this;
    }

    public function addTracking(Tracking $tracking): static
    {
        $this->trackings = [...$this->trackings, $tracking];

        return $this;
    }

    public function removeTracking(Tracking $tracking): static
    {
        $this->trackings = \array_filter(
            $this->trackings,
            function (Tracking $existingTracking) use ($tracking) {
                return $existingTracking->title !== $tracking->title
                    && $existingTracking->value !== $tracking->value;
            }
        );

        return $this;
    }
}
