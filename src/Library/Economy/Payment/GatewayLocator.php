<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayCheckout;

class GatewayLocator
{
    /** @var GatewayInterface[] */
    private array $gateways;

    public function __construct(iterable $gateways)
    {
        /** @var GatewayInterface[] */
        $gateways = \iterator_to_array($gateways);

        foreach ($gateways as $gateway) {
            $this->gateways[$gateway->getName()] = $gateway;
        }
    }

    /**
     * @return array<string> List of the available Gateway names
     */
    public function getNames(): array
    {
        return \array_keys($this->gateways);
    }

    /**
     * @return array<string> List of the available Gateway names (hardcoded)
     */
    public static function getNamesStatic(): array
    {
        return [
            StripeGateway::getName(),
            WalletGateway::getName()
        ];
    }

    /**
     * @return GatewayInterface[]
     */
    public function getGateways(): array
    {
        return $this->gateways;
    }

    /**
     * @param string $name Name of the Gateway interface implementation
     * @return GatewayInterface
     * @throws \Exception When the $name does not match to that of an implemented Gateway
     */
    public function getGateway(string $name): GatewayInterface
    {
        if (!\array_key_exists($name, $this->gateways)) {
            throw new \Exception("No such Gateway with the name '$name'.");
        }

        return $this->gateways[$name];
    }

    /**
     * @param GatewayCheckout $gatewayCheckout
     * @return GatewayInterface
     * @throws \Exception When the gateway name in the GatewayCheckout does not match that of an implemented Gateway
     */
    public function getGatewayByCheckout(GatewayCheckout $gatewayCheckout): GatewayInterface
    {
        return $this->getGateway($gatewayCheckout->getGatewayName());
    }
}
