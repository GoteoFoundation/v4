<?php

namespace App\DependencyInjection;

use App\Repository\SystemVarRepository;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
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
        try {
            $systemVars = $this->systemVarRepository->findAll();
        } catch (ConnectionException $e) {
            // Ignore loader in environments where the database is not yet setup
            return [];
        } catch (TableNotFoundException $e) {
            // Ignore loader in environments where the table does not exist
            return [];
        }

        $processedVars = [];
        foreach ($systemVars as $systemVar) {
            $processedVars[$systemVar->getName()] = $systemVar->getValue();
        }

        return $processedVars;
    }
}
