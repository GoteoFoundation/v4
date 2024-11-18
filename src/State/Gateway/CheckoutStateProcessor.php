<?php

namespace App\State\Gateway;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Gateway as Resource;
use App\Gateway\GatewayLocator;
use App\Mapping\Gateway\CheckoutMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CheckoutStateProcessor implements ProcessorInterface
{
    public function __construct(
        private CheckoutMapper $checkoutMapper,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $innerProcessor,
        private GatewayLocator $gatewayLocator,
    ) {}

    /**
     * @param Resource\Checkout $data
     *
     * @return Resource\Checkout
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $entity = $this->checkoutMapper->toEntity($data);
        $entity = $this->innerProcessor->process($entity, $operation, $uriVariables, $context);

        $entity = $this->gatewayLocator->get($data->gateway->name)->process($entity);
        $entity = $this->innerProcessor->process($entity, $operation, $uriVariables, $context);

        return $this->checkoutMapper->toResource($entity);
    }
}
