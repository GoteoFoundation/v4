<?php

namespace App\ApiResource;

class EmbeddedResource
{
    /**
     * ID of the embedded resource.
     */
    public int $id;

    /**
     * Path to the embedded resource.
     */
    public string $iri;

    /**
     * Actual object data of the embedded resource.
     *
     * @var array<string, mixed>
     */
    public mixed $resource;
}
