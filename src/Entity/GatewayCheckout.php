<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata as API;
use App\Entity\Trait\TimestampableCreationEntity;
use App\Entity\Trait\TimestampableUpdationEntity;
use App\Repository\GatewayCheckoutRepository;
use App\State\GatewayCheckoutProcessor;
use App\Validator\GatewayName;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCheckout bundles the data to perform a payment operation with a Gateway.\
 * \
 * Once the Gateway validates the payment as successful the GatewayCheckout will be updated
 * and respective AccountingTransactions will be generated for each GatewayCharge.
 */
#[Gedmo\Loggable()]
#[API\GetCollection()]
#[API\Post(processor: GatewayCheckoutProcessor::class)]
#[API\Get()]
#[API\ApiFilter(filterClass: SearchFilter::class, properties: ['origin' => 'exact', 'charges.target' => 'exact'])]
#[ORM\Entity(repositoryClass: GatewayCheckoutRepository::class)]
#[ORM\Index(fields: ['migratedId'])]
class GatewayCheckout
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
    private ?GatewayCheckoutStatus $status = null;

    /**
     * The GatewayCharges to be charged at checkout with the gateway.
     *
     * @var Collection<int, GatewayCharge>
     */
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1)]
    #[ORM\OneToMany(mappedBy: 'checkout', targetEntity: GatewayCharge::class, cascade: ['persist'])]
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
     * @var GatewayLink[]
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private array $gatewayLinks = [];

    /**
     * A list of tracking codes provided by the Gateway for this checkout.\
     * e.g: Order ID, Payment Capture ID, Checkout Session Token.
     *
     * @var GatewayTracking[]
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private array $gatewayTrackings = [];

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

    /**
     * A free-form collection of additional data associated with this checkout operation.
     */
    #[ORM\Column(nullable: true)]
    private ?array $metadata = null;

    public function __construct()
    {
        $this->status = GatewayCheckoutStatus::Pending;
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

    public function getStatus(): ?GatewayCheckoutStatus
    {
        return $this->status;
    }

    public function setStatus(GatewayCheckoutStatus $status): static
    {
        $this->status = $status;

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

    /**
     * @return GatewayLink[]
     */
    public function getGatewayLinks(): array
    {
        return $this->gatewayLinks;
    }

    public function addGatewayLink(GatewayLink $link): static
    {
        $this->gatewayLinks = [...$this->gatewayLinks, $link];

        return $this;
    }

    public function removeGatewayLink(GatewayLink $link): static
    {
        $this->gatewayLinks = \array_filter(
            $this->gatewayLinks,
            function (GatewayLink $existingLink) use ($link) {
                return $existingLink->href !== $link->href;
            }
        );

        return $this;
    }

    /**
     * @return GatewayTracking[]
     */
    public function getGatewayTrackings(): array
    {
        return $this->gatewayTrackings;
    }

    public function addGatewayTracking(GatewayTracking $tracking): static
    {
        $this->gatewayTrackings = [...$this->gatewayTrackings, $tracking];

        return $this;
    }

    public function removeGatewayTracking(GatewayTracking $tracking): static
    {
        $this->gatewayTrackings = \array_filter(
            $this->gatewayTrackings,
            function (GatewayTracking $existingTracking) use ($tracking) {
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

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }
}
