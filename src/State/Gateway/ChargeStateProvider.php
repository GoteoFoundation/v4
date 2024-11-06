<?php

namespace App\State\Gateway;

use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Mapping\Gateway\ChargeMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ChargeStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
        private ChargeMapper $chargeMapper,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $charge = $this->itemProvider->provide($operation, $uriVariables, $context);

        if ($charge === null) {
            return null;
        }

        return $this->chargeMapper->toResource($charge);
    }
}
