<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\GatewayCheckoutLinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GatewayCheckoutLinkRepository::class)]
class GatewayCheckoutLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The complete target URL.
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $href = null;

    /**
     * The link relation type, which serves as an ID for a link that unambiguously describes the semantics of the link.
     *
     * @see https://www.iana.org/assignments/link-relations/link-relations.xhtml
     */
    #[ORM\Column(length: 255)]
    private ?string $rel = null;

    /**
     * The HTTP method required to make the related call.
     */
    #[ORM\Column(length: 255)]
    private ?string $method = null;

    /**
     * The type of the link indicates who is the intended user of a link.\
     * `debug` links are for developers and platform maintainers to get useful information about the checkout.\
     * `payment` links are for end-users who must visit this link to complete the checkout.
     */
    #[ORM\Column(enumType: GatewayCheckoutLinkType::class)]
    private ?GatewayCheckoutLinkType $type = null;

    #[API\ApiProperty(readable: false)]
    #[ORM\ManyToOne(inversedBy: 'links')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GatewayCheckout $checkout = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHref(): ?string
    {
        return $this->href;
    }

    public function setHref(string $href): static
    {
        $this->href = $href;

        return $this;
    }

    public function getRel(): ?string
    {
        return $this->rel;
    }

    public function setRel(string $rel): static
    {
        $this->rel = $rel;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getType(): ?GatewayCheckoutLinkType
    {
        return $this->type;
    }

    public function setType(GatewayCheckoutLinkType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCheckout(): ?GatewayCheckout
    {
        return $this->checkout;
    }

    public function setCheckout(?GatewayCheckout $checkout): static
    {
        $this->checkout = $checkout;

        return $this;
    }
}
