<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Validator\GatewayName;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GatewayCheckouts represent an User's attempt to perform a payment with an specific Gateway.
 */
#[ORM\Embeddable]
class TransactionGateway
{
    /**
     * The Gateway processing this Transaction.
     */
    #[GatewayName]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\NotBlank()]
    #[Assert\Url()]
    #[ORM\Column(length: 255)]
    private ?string $successUrl;

    #[Assert\Url()]
    #[ORM\Column(length: 255)]
    private ?string $failureUrl;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
}
