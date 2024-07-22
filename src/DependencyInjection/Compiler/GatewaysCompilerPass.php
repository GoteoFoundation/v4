<?php

namespace App\DependencyInjection\Compiler;

use App\Library\Economy\Payment\GatewayInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GatewaysCompilerPass implements CompilerPassInterface
{
    public const GATEWAYS_DIR = 'gateways';
    public const GATEWAY_NAMES_LOCK = 'gateway_names_compiled.lock';

    /**
     * @return string The path to the gateways dir inside the project's var dir
     */
    public static function getCompileDir(): string
    {
        return sprintf(
            '%s%svar%s%s',
            \dirname(__DIR__, 3),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            self::GATEWAYS_DIR,
        );
    }

    private static function makeCompileDir()
    {
        $compileDir = self::getCompileDir();

        if (!\is_dir($compileDir)) {
            \mkdir($compileDir, 0777, true);
        }
    }

    /**
     * @return string The path to the gateway names lock file
     */
    public static function getLockFile(): string
    {
        return sprintf(
            '%s%s%s',
            self::getCompileDir(),
            DIRECTORY_SEPARATOR,
            self::GATEWAY_NAMES_LOCK
        );
    }

    private static function writeLockFile(array $lines)
    {
        self::makeCompileDir();

        \file_put_contents(
            self::getLockFile(),
            implode(PHP_EOL, $lines)
        );
    }

    private static function getGatewaysNamespace(): string
    {
        return join('\\', \array_slice(explode('\\', GatewayInterface::class), 0, -1));
    }

    private function getGatewayNames(array $gatewayClasses): array
    {
        $names = [];
        foreach ($gatewayClasses as $class) {
            $names[] = $class::getName();
        }

        return $names;
    }

    private static function getGatewayClasses(string $classesDir): array
    {
        $namespace = self::getGatewaysNamespace();
        $economyDirPaths = \scandir($classesDir);

        $classes = [];
        foreach ($economyDirPaths as $path) {
            if ($path === '.' || $path === '..') {
                continue;
            }

            $className = \rtrim($path, '.php');
            if (!\str_ends_with($className, 'Gateway')) {
                continue;
            }

            $reflection = new \ReflectionClass(sprintf("%s\%s", $namespace, $className));
            if ($reflection->isAbstract()) {
                continue;
            }

            $classes[] = $reflection->getName();
        }

        return $classes;
    }

    /**
     * Ensures the gateway names are unique for each gateway.
     *
     * @param array $gatewayClasses Fully-qualified Gateway class names
     *
     * @throws \Exception If there are two different Gateway classes with the same name
     *
     * @see \App\Library\Economy\Payment\GatewayInterface::getName() To see the names returned by the implementations
     */
    public static function validateGatewayNames(array $gatewayClasses): void
    {
        $gatewaysValidated = [];
        foreach ($gatewayClasses as $gatewayClass) {
            $gatewayName = $gatewayClass::getName();

            if (\array_key_exists($gatewayName, $gatewaysValidated)) {
                $exceptionMessage = sprintf(
                    "Duplicate Gateway name '%s' from class %s, name is already in use by class %s",
                    $gatewayName,
                    $gatewayClass,
                    $gatewaysValidated[$gatewayName]
                );

                throw new \Exception($exceptionMessage);
            }

            $gatewaysValidated[$gatewayName] = $gatewayClass;
        }
    }

    /**
     * Stores the gateway names in disk.
     *
     * @param array $names The names returned by the gateways
     *
     * @see \App\Library\Economy\Payment\GatewayInterface::getName() To see the names returned by the implementations
     */
    public static function compileGatewayNames(array $names): void
    {
        self::writeLockFile($names);
    }

    public function process(ContainerBuilder $container): void
    {
        $classesDir = join(DIRECTORY_SEPARATOR, [
            $container->getParameter('kernel.project_dir'),
            'src',
            ...\array_slice(explode('\\', self::getGatewaysNamespace()), 1),
        ]);

        $gatewayClasses = self::getGatewayClasses($classesDir);
        self::validateGatewayNames($gatewayClasses);

        $gatewayNames = $this->getGatewayNames($gatewayClasses);
        self::compileGatewayNames($gatewayNames);
    }
}
