<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
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
     * The GatewayCharges to be charged at checkout with the Gateway.
     */
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1)]
    #[ORM\ManyToMany(targetEntity: GatewayCharge::class)]
    private Collection $charges;

    /**
     * The status of the checkout with the Gateway.
     */
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
     * An external identifier provided by the Gateway for the payment.\
     * Required when a GatewayCheckout is completed.
     */
    #[API\ApiFilter(SearchFilter::class)]
    #[ORM\Column(length: 255)]
    private ?string $gatewayReference = null;

    /**
     * GatewayCheckout was migrated from an invest record in Goteo v3 platform. 
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column]
    private ?bool $migrated = null;

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
