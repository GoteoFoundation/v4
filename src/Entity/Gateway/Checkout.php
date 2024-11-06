<?php

namespace App\Entity\Gateway;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata as API;
use App\Entity\Accounting\Accounting;
use App\Entity\Trait\TimestampableCreationEntity;
use App\Entity\Trait\TimestampableUpdationEntity;
use App\Gateway\CheckoutStatus;
use App\Gateway\Link;
use App\Gateway\Tracking;
use App\Repository\Gateway\CheckoutRepository;
use App\State\Gateway\CheckoutStateProcessor;
use App\Validator\GatewayName;
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
#[API\GetCollection()]
#[API\Post(processor: CheckoutStateProcessor::class)]
#[API\Get()]
#[API\ApiFilter(filterClass: SearchFilter::class, properties: ['origin' => 'exact', 'charges.target' => 'exact'])]
#[ORM\Entity(repositoryClass: CheckoutRepository::class)]
#[ORM\Index(fields: ['migratedId'])]
class Checkout
{
    use TimestampableCreationEntity;
    use TimestampableUpdationEntity;

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
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(SearchFilter::class)]
    #[ORM\Column()]
    private ?CheckoutStatus $status = null;

    /**
     * The GatewayCharges to be charged at checkout with the gateway.
     *
     * @var Collection<int, Charge>
     */
    #[API\ApiProperty(readableLink: true, writableLink: true)]
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1)]
    #[SupportedChargeTypes()]
    #[ORM\OneToMany(mappedBy: 'checkout', targetEntity: Charge::class, cascade: ['persist'])]
    private Collection $charges;

    /**
     * The name of the Gateway implementation to checkout with.
     */
    #[GatewayName]
    #[Assert\NotBlank()]
    #[API\ApiFilter(SearchFilter::class)]
    #[ORM\Column(length: 255)]
    private ?string $gateway = null;

    /**
     * A list of URLs provided by the Gateway for this checkout.\
     * e.g: Fulfill payment, API resource address.
     *
     * @var Link[]
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private array $links = [];

    /**
     * A list of tracking codes provided by the Gateway for this checkout.\
     * e.g: Order ID, Payment Capture ID, Checkout Session Token.
     *
     * @var Tracking[]
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private array $trackings = [];

    /**
     * GatewayCheckout was migrated from an invest record in Goteo v3 platform.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private ?bool $migrated = false;

    /**
     * The id of the original invest record in the Goteo v3 platform.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $migratedId = null;

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

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    public function setGateway(string $gateway): static
    {
        $this->gateway = $gateway;

        return $this;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
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
}
