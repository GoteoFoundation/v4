<?php

namespace App\DependencyInjection;

use App\Repository\SystemVarRepository;
use Symfony\Component\DependencyInjection\EnvVarLoaderInterface;

/**
 * Loads SystemVar records as environment variables for the code to use.
 */
final class SystemVarLoader implements EnvVarLoaderInterface
{
    public function __construct(
        private SystemVarRepository $systemVarRepository
    ) {
    }

    public function loadEnvVars(): array
    {
        $systemVars = $this->systemVarRepository->findAll();

        $processedVars = [];
        foreach ($systemVars as $systemVar) {
            $processedVars[$systemVar->getName()] = $systemVar->getValue();
        }

        return $processedVars;
    }
}
