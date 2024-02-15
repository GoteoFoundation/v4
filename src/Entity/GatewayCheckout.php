<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\GatewayCheckoutRepository;
use App\State\GatewayCheckoutStateProcessor;
use App\Validator\GatewayName;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GatewayCheckouts represent an User's attempt to perform a payment with an specific Gateway.
 */
#[ORM\Entity(repositoryClass: GatewayCheckoutRepository::class)]
#[API\ApiResource]
#[API\GetCollection()]
#[API\Post(processor: GatewayCheckoutStateProcessor::class)]
#[API\Get()]
class GatewayCheckout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[GatewayName]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $gateway = null;

    /**
     * The money to be paid at the Gateway.
     */
    #[Assert\Valid()]
    #[Assert\NotBlank()]
    #[ORM\Embedded(class: Money::class)]
    private ?Money $money = null;

    public function getId(): ?int
    {
        return $this->id ?? 0;
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

    public function getMoney(): ?Money
    {
        return $this->money;
    }

    public function setMoney(Money $money): static
    {
        $this->money = $money;

        return $this;
    }
}
