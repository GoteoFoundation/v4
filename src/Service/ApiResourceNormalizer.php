<?php

namespace App\Service;

/**
 * Normalizes between Entity classnames and API resource names
 */
class ApiResourceNormalizer
{
    /**
     * Converts an Entity class name into an API resource name
     */
    public static function toResource(string $className): string
    {
        $classPieces = explode("\\", $className);

        return strtolower(end($classPieces));
    }

    /**
     * Converts to an Entity class name from an API resource name
     */
    public static function toEntity(string $resourceName): string
    {
        return sprintf("App\\Entity\\%s", ucfirst(strtolower($resourceName)));
    }
}
