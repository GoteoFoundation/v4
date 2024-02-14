<?php

namespace App\DependencyInjection\Compiler;

use App\Library\Economy\Payment\GatewayLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GatewayPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var GatewayLocator */
        $gatewayLocator = $container->get(GatewayLocator::class);

        $gatewayLocator->validateGatewayNames();
        $gatewayLocator->compileGatewayNames();
    }
}
