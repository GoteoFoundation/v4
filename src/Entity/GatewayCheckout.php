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
     * The GatewayCharges to be charged at checkout with the Gateway.
     */
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1)]
    #[API\ApiProperty(readableLink: true, writableLink: true)]
    #[ORM\ManyToMany(targetEntity: GatewayCharge::class, cascade: ['persist'])]
    private Collection $charges;

    /**
     * The status of the checkout with the Gateway.
     */
    #[Gedmo\Versioned]
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(SearchFilter::class)]
    #[ORM\Column()]
    private ?GatewayCheckoutStatus $status = null;

    /**
     * The name of the Gateway implementation to checkout with.
     */
    #[GatewayName]
    #[Assert\NotBlank()]
    #[API\ApiFilter(SearchFilter::class)]
    #[ORM\Column(length: 255)]
    private ?string $gateway = null;

    /**
     * An external identifier provided by the Gateway for the payment.
     */
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(SearchFilter::class)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gatewayReference = null;

    /**
     * The URLs provided by the Gateway for this checkout.
     *
     * @var Collection<int, GatewayCheckoutLink>
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\OneToMany(mappedBy: 'checkout', targetEntity: GatewayCheckoutLink::class)]
    private Collection $links;

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
    private ?string $migratedReference = null;

    /**
     * A free-form collection of additional data associated with this checkout operation.
     */
    #[ORM\Column(nullable: true)]
    private ?array $metadata = null;

    public function __construct()
    {
        $this->charges = new ArrayCollection();
        $this->status = GatewayCheckoutStatus::Pending;
        $this->links = new ArrayCollection();
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
        }

        return $this;
    }

    public function removeCharge(GatewayCharge $charge): static
    {
        $this->charges->removeElement($charge);

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

    /**
     * @return Collection<int, GatewayCheckoutLink>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(GatewayCheckoutLink $link): static
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setCheckout($this);
        }

        return $this;
    }

    public function removeLink(GatewayCheckoutLink $link): static
    {
        if ($this->links->removeElement($link)) {
            // set the owning side to null (unless already changed)
            if ($link->getCheckout() === $this) {
                $link->setCheckout(null);
            }
        }

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

    public function getMigratedReference(): ?string
    {
        return $this->migratedReference;
    }

    public function setMigratedReference(?string $migratedReference): static
    {
        $this->migratedReference = $migratedReference;

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
