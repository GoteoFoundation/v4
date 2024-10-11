<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\GatewayCheckout;
use App\Library\Economy\Payment\GatewayLocator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GatewayCheckoutProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $innerProcessor,
        private GatewayLocator $gatewayLocator,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @param GatewayCheckout $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $checkout = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        $gateway = $this->gatewayLocator->getGatewayOf($checkout);
        $checkout = $gateway->process($checkout);

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
