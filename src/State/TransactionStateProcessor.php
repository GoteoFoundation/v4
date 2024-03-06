<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Transaction;
use App\Library\Economy\Payment\GatewayLocator;
use Doctrine\ORM\EntityManagerInterface;

class TransactionStateProcessor implements ProcessorInterface
{
    public function __construct(
        private GatewayLocator $gatewayLocator,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param Transaction $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Transaction
    {
        $gateway = $this->gatewayLocator->getGateway($data->getGateway());

        $gateway->process($data);

        return $data;
    }
}
