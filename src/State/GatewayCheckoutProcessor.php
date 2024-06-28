<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\GatewayCheckout;
use App\Library\Economy\Payment\GatewayLocator;
use Doctrine\ORM\EntityManagerInterface;

class GatewayCheckoutProcessor implements ProcessorInterface
{
    public function __construct(
        private GatewayLocator $gatewayLocator,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param GatewayCheckout $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): GatewayCheckout
    {
        $gateway = $this->gatewayLocator->getGatewayOf($data);

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $checkout = $gateway->create($data);

        $this->entityManager->persist($checkout);
        $this->entityManager->flush();

        return $checkout;
    }
}
