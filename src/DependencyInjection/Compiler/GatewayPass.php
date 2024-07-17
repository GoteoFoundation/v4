<?php

namespace App\DependencyInjection\Compiler;

use App\Library\Economy\Payment\GatewayLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GatewayPass implements CompilerPassInterface
{
    public const GATEWAYS_DIR = 'gateways';
    public const GATEWAY_NAMES_LOCK = 'gateway_names_compiled.lock';

    public static function getGatewayCompileDir(): string
    {
        return sprintf(
            '%s%svar%s%s',
            \dirname(__DIR__, 3),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            self::GATEWAYS_DIR,
        );
    }

    public static function getGatewayNamesLockFile(): string
    {
        return sprintf(
            '%s%s%s',
            self::getGatewayCompileDir(),
            DIRECTORY_SEPARATOR,
            self::GATEWAY_NAMES_LOCK
        );
    }

    /**
     * Generates a directory for the gateways in the project var dir.
     */
    public static function makeCompileDir()
    {
        $compileDir = self::getGatewayCompileDir();

        if (!\is_dir($compileDir)) {
            \mkdir($compileDir, 0777, true);
        }
    }

    /**
     * Stores the gateway names in disk.
     * 
     * @param array $names The names returned by the interfaces
     */
    public static function compileGatewayNames(array $names)
    {
        self::makeCompileDir();

        \file_put_contents(
            self::getGatewayNamesLockFile(),
            implode(PHP_EOL, $names)
        );
    }

    private function getGatewayClasses(string $kernelDir): array
    {
        $classes = [];

        $namespacePieces = \array_slice(explode('\\', GatewayLocator::class), 0, -1);

        $economyDir = join(DIRECTORY_SEPARATOR, [
            $kernelDir,
            'src',
            ...\array_slice($namespacePieces, 1)
        ]);

        $economyDirPaths = \scandir($economyDir);
        foreach ($economyDirPaths as $path) {
            if ($path === "." || $path === "..") {
                continue;
            }

            $className = \rtrim($path, '.php');
            if (!\str_ends_with($className, 'Gateway')) {
                continue;
            }

            $reflection = new \ReflectionClass(sprintf("%s\%s", join("\\", $namespacePieces), $className));
            if ($reflection->isAbstract()) {
                continue;
            }

            $classes[] = $reflection->getName();
        }

        return $classes;
    }

    /**
     * @throws \Exception If there are two different Gateway classes with the same name
     *
     * @param array $gatewayClasses Fully-qualified Gateway class names
     * @see GatewayInterface::getName()
     */
    private function validateGatewayNames(array $gatewayClasses)
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
     * @param array $gatewayClasses Fully-qualified Gateway class names
     * @return array The names returned by each interface
     */
    private function getGatewayNames(array $gatewayClasses): array
    {
        $names = [];
        foreach ($gatewayClasses as $class) {
            $names[] = $class::getName();
        }

        return $names;
    }

    public function process(ContainerBuilder $container): void
    {
        $gatewayClasses = $this->getGatewayClasses($container->getParameter('kernel.project_dir'));
        $this->validateGatewayNames($gatewayClasses);

        $gatewayNames = $this->getGatewayNames($gatewayClasses);
        self::compileGatewayNames($gatewayNames);
    }
}
