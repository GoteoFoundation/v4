<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\GatewayCheckoutRepository;
use App\State\GatewayCheckoutStateProcessor;
use App\Validator\GatewayName;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Gateway Checkout represents a User's session as they perform a payment with a Gateway.
 */
#[ORM\Entity(repositoryClass: GatewayCheckoutRepository::class)]
#[API\ApiResource(processor:GatewayCheckoutStateProcessor::class)]
class GatewayCheckout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The money to be paid by the User at the Checkout.
     */
    #[ORM\Embedded(class: Money::class)]
    #[Assert\NotBlank()]
    #[Assert\Valid()]
    private ?object $money = null;

    /**
     * The name of the Gateway with which to perform the Checkout.
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[GatewayName]
    private ?string $gatewayName = null;

    /**
     * The URL to redirect after a successful Checkout with the Gateway.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank()]
    #[Assert\Url()]
    private ?string $successUrl = null;

    /**
     * The URL to redirect after a failed Checkout with the Gateway.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank()]
    #[Assert\Url()]
    private ?string $failureUrl = null;

    /**
     * The URL the User must visit to perform the Checkout with the Gateway.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[API\ApiProperty(writable: false)]
    #[Assert\Url()]
    private ?string $checkoutUrl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMoney(): ?object
    {
        return $this->money;
    }

    public function setMoney(object $money): static
    {
        $this->money = $money;

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

    public function getSuccessUrl(): ?string
    {
        return $this->successUrl;
    }

    public function setSuccessUrl(string $successUrl): static
    {
        $this->successUrl = $successUrl;

        return $this;
    }

    public function getFailureUrl(): ?string
    {
        return $this->failureUrl;
    }

    public function setFailureUrl(string $failureUrl): static
    {
        $this->failureUrl = $failureUrl;

        return $this;
    }

    public function getCheckoutUrl(): ?string
    {
        return $this->checkoutUrl;
    }

    public function setCheckoutUrl(string $checkoutUrl): static
    {
        $this->checkoutUrl = $checkoutUrl;

        return $this;
    }
}
