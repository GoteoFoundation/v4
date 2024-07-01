<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayCheckout;

class GatewayLocator
{
    public const GATEWAYS_DIR = 'gateways';
    public const GATEWAY_NAMES_LOCK = 'gateway_names_compiled.lock';

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

    /**
     * @throws \Exception If there are two different Gateway classes with the same name
     *
     * @see GatewayInterface::getName()
     */
    public function validateGatewayNames()
    {
        $gatewaysValidated = [];
        foreach ($this->gatewaysByClass as $class => $gateway) {
            $gatewayName = $gateway::getName();

            if (\array_key_exists($gatewayName, $gatewaysValidated)) {
                $exceptionMessage = sprintf(
                    "Duplicate Gateway name '%s' from class %s, name is already in use by class %s",
                    $gatewayName,
                    $gateway::class,
                    $gatewaysValidated[$gatewayName]
                );

                throw new \Exception($exceptionMessage);
            }

            $gatewaysValidated[$gatewayName] = $class;
        }
    }

    private static function getGatewayCompileDir(): string
    {
        return sprintf(
            '%s%svar%s%s',
            \dirname(__DIR__, 4),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            self::GATEWAYS_DIR,
        );
    }

    private static function getGatewayNamesLockFile(): string
    {
        return sprintf(
            '%s%s%s',
            self::getGatewayCompileDir(),
            DIRECTORY_SEPARATOR,
            self::GATEWAY_NAMES_LOCK
        );
    }

    /**
     * Generates a directory for the gateways in the 'bundles' dir.
     */
    public function makeCompileDir()
    {
        $CompileDir = self::getGatewayCompileDir();

        if (!\is_dir($CompileDir)) {
            \mkdir($CompileDir, 0777, true);
        }
    }

    /**
     * Stores the available gateway names in disk.
     */
    public function compileGatewayNames()
    {
        $this->makeCompileDir();

        \file_put_contents(
            self::getGatewayNamesLockFile(),
            implode(PHP_EOL, $this->getNames())
        );
    }

    /**
     * @return array<string> List of the available Gateway names
     */
    public function getNames(): array
    {
        return \array_keys($this->gatewaysByName);
    }

    /**
     * @return array<string> List of the available Gateway names
     *
     * @see compileGatewayNames()
     */
    public static function getNamesStatic(): array
    {
        return explode(PHP_EOL, \file_get_contents(self::getGatewayNamesLockFile()));
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
    public function getGatewayOf(GatewayCheckout $checkout): GatewayInterface
    {
        $gateway = $checkout->getGateway();
        if (!$gateway) {
            throw new \Exception('The given GatewayCheckout does not specify a Gateway');
        }

        return $this->getGateway($gateway);
    }
}
