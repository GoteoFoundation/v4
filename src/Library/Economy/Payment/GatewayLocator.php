<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayCheckout;

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
        
        foreach ($this->gatewaysByClass as $class => $gateway) {
            $this->gatewaysByName[$gateway::getName()] = $gateway;
        }
    }

    public function validateGatewayNames()
    {
        $gatewaysValidated = [];
        foreach ($this->gatewaysByClass as $class => $gateway) {
            $gatewayName = $gateway::getName();

            if (\array_key_exists($gatewayName, $gatewaysValidated)) {
                throw new \Exception(sprintf(
                    "Duplicate Gateway name '%s' from class %s, name is already in use by class %s",
                    $gatewayName,
                    $gateway::class,
                    $gatewaysValidated[$gatewayName]
                ));
            }

            $gatewaysValidated[$gatewayName] = $class;
        }
    }

    /**
     * @return array<string> List of the available Gateway names
     */
    public function getNames(): array
    {
        return \array_keys($this->gatewaysByName);
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
        return $this->gatewaysByName;
    }

    /**
     * @param string $name Name of the Gateway interface implementation
     * @return GatewayInterface
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
     * @param GatewayCheckout $gatewayCheckout
     * @return GatewayInterface
     * @throws \Exception When the gateway name in the GatewayCheckout does not match that of an implemented Gateway
     */
    public function getGatewayByCheckout(GatewayCheckout $gatewayCheckout): GatewayInterface
    {
        return $this->getGateway($gatewayCheckout->getGatewayName());
    }
}
