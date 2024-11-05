<?php

namespace App\Gateway;

use App\DependencyInjection\Compiler\GatewaysCompilerPass;
use App\Entity\Gateway\Checkout;

class GatewayLocator
{
    /** @var GatewayInterface[] */
    private array $gatewaysByName = [];

    /** @var GatewayInterface[] */
    private array $gatewaysByClass = [];

    public function __construct(iterable $instanceof)
    {
        foreach (\iterator_to_array($instanceof) as $key => $gateway) {
            $this->gatewaysByClass[$gateway::class] = $gateway;
        }

        GatewaysCompilerPass::validateGatewayNames($this->gatewaysByClass);

        foreach ($this->gatewaysByClass as $class => $gateway) {
            $this->gatewaysByName[$gateway::getName()] = $gateway;
        }
    }

    /**
     * @return array<string> List of the fully-qualified class names of the available interfaces
     */
    public function getClasses(): array
    {
        return \array_keys($this->gatewaysByClass);
    }

    /**
     * @return array<string> List of names of the available interfaces
     */
    public function getNames(): array
    {
        return \array_keys($this->gatewaysByName);
    }

    /**
     * @return array<string> List of the available Gateway names
     *
     * @see \App\DependencyInjection\Compiler\GatewaysCompilerPass::compileGatewayNames()
     */
    public static function getNamesStatic(): array
    {
        return explode(PHP_EOL, \file_get_contents(GatewaysCompilerPass::getLockFile()));
    }

    /**
     * @return GatewayInterface[]
     */
    public function getGateways(): array
    {
        return $this->gatewaysByName;
    }

    /**
     * @param string $name Name of the Gateway interface implementation
     *
     * @throws \Exception When the $name does not match to that of an implemented Gateway
     */
    public function getGateway(string $name): GatewayInterface
    {
        if (!\array_key_exists($name, $this->gatewaysByName)) {
            throw new \Exception("No such Gateway with the name $name");
        }

        return $this->gatewaysByName[$name];
    }

    /**
     * @throws \Exception When the $checkout::gateway does not match to that of an implemented Gateway
     */
    public function getGatewayOf(Checkout $checkout): GatewayInterface
    {
        $gateway = $checkout->getGateway();
        if (!$gateway) {
            throw new \Exception('The given GatewayCheckout does not specify a Gateway');
        }

        return $this->getGateway($gateway);
    }
}
