<?php

namespace App\Gateway;

use App\Entity\Gateway\Checkout;
use App\Gateway\Exception\DuplicateGatewayException;

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

        self::validateGatewayNames($this->gatewaysByClass);

        foreach ($this->gatewaysByClass as $class => $gateway) {
            $this->gatewaysByName[$gateway::getName()] = $gateway;
        }
    }

    /**
     * @return <string, GatewayInterface>
     */
    public function getAll(): array
    {
        return $this->gatewaysByName;
    }

    /**
     * @param string $name Name of the Gateway interface implementation
     *
     * @throws \Exception When the `$name` does not match to that of an implemented Gateway
     */
    public function get(string $name): GatewayInterface
    {
        if (!\array_key_exists($name, $this->gatewaysByName)) {
            throw new \Exception("Could not match '$name' to the name of any available Gateway");
        }

        return $this->gatewaysByName[$name];
    }

    /**
     * @throws \Exception When the $checkout::gateway does not match to that of an implemented Gateway
     */
    public function getByCheckout(Checkout $checkout): GatewayInterface
    {
        $gateway = $checkout->getGatewayName();
        if (!$gateway) {
            throw new \Exception('The given Gateway Checkout does not specify a Gateway');
        }

        return $this->get($gateway);
    }

    /**
     * Ensures the gateway names are unique for each gateway.
     *
     * @param array $gatewayClasses Fully-qualified Gateway class names
     *
     * @throws \Exception If there are two different Gateway classes that return the same name string
     */
    private static function validateGatewayNames(array $gatewayClasses): void
    {
        $gatewaysValidated = [];
        foreach ($gatewayClasses as $gatewayClass) {
            $gatewayName = $gatewayClass::getName();

            if (\array_key_exists($gatewayName, $gatewaysValidated)) {
                throw new DuplicateGatewayException(
                    $gatewayName,
                    $gatewayClass::class,
                    $gatewaysValidated[$gatewayName]::class
                );
            }

            $gatewaysValidated[$gatewayName] = $gatewayClass;
        }
    }
}
