<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\GatewayCheckout;
use App\Library\Economy\Payment\GatewayLocator;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class GatewayCheckoutStateProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private GatewayLocator $gatewayLocator,
    ) {
    }

    /**
     * @return T2
     * @inheritdoc
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof GatewayCheckout) {
            $gateway = $this->gatewayLocator->getGatewayOf($data);
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
