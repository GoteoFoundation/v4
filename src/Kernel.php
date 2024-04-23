<?php

namespace App;

use App\DependencyInjection\Compiler\GatewayPass;
use App\DependencyInjection\Compiler\VersionedResourcePass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new GatewayPass,
            PassConfig::TYPE_AFTER_REMOVING
        );

        $container->addCompilerPass(
            new VersionedResourcePass,
            PassConfig::TYPE_AFTER_REMOVING
        );
    }
}
