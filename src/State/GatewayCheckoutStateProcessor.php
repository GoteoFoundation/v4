<?php

namespace App\State;

use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\GatewayCheckout;
use App\Library\Economy\Payment\GatewayLocator;
use Doctrine\ORM\EntityManagerInterface;

class GatewayCheckoutStateProcessor implements ProcessorInterface
{
    public function __construct(
        private GatewayLocator $gatewayLocator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): GatewayCheckout
    {
        /** @var GatewayCheckout */
        $gatewayCheckout = $data;

        switch ($operation::class) {
            case API\Post::class:
                $gatewayCheckout = $this->gatewayLocator
                    ->getGatewayByCheckout($gatewayCheckout)
                    ->process($gatewayCheckout);
                break;
            default:
                # code...
                break;
        }

        $this->entityManager->persist($gatewayCheckout);
        $this->entityManager->flush();

        return $gatewayCheckout;
    }
}
