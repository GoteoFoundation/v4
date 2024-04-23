<?php

namespace App\Service;

class VersionedResourceService
{
    public const VERSIONED_RESOURCES_DIR = 'versioned_resources';
    public const VERSIONED_RESOURCE_NAMES_LOCK = 'versioned_resource_names_compiled.lock';

    private static function getCompileDir(): string
    {
        return sprintf(
            '%s%svar%s%s',
            \dirname(__DIR__, 2),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            self::VERSIONED_RESOURCES_DIR,
        );
    }

    private static function getNamesLockFile(): string
    {
        return sprintf(
            '%s%s%s',
            self::getCompileDir(),
            DIRECTORY_SEPARATOR,
            self::VERSIONED_RESOURCE_NAMES_LOCK
        );
    }

    /**
     * Generates a directory for the service files.
     */
    public function makeCompileDir()
    {
        $compileDir = self::getCompileDir();

        if (!\is_dir($compileDir)) {
            \mkdir($compileDir, 0777, true);
        }
    }

    /**
     * Stores the given names in lock file.
     * @param string[] $names
     */
    public function compileNames(array $names)
    {
        $this->makeCompileDir();

        \file_put_contents(
            self::getNamesLockFile(),
            implode(PHP_EOL, $names)
        );
    }

    /**
     * @return array<string> List of the versioned resource names
     * @see compileNames()
     */
    public static function getNames(): array
    {
        return explode(PHP_EOL, \file_get_contents(self::getNamesLockFile()));
    }
}
