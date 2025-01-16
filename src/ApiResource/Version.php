<?php

namespace App\ApiResource;

use ApiPlatform\Metadata as API;
use App\Filter\ResourceVersionResourceFilter;
use App\Filter\ResourceVersionResourceIdFilter;
use App\State\ResourceVersionStateProvider;

/**
 * Some resources are versioned. This means v4 keeps track of the changes performed in subsets of specific properties within these resources.\
 * \
 * This allows us to keep track of the flow and the evolution of records in the platform.
 * Looking at the changes done between one version and the next one we can reconstruct how a resource was at a certain point in time.
 */
#[API\ApiFilter(ResourceVersionResourceFilter::class, properties: ['resource'])]
#[API\ApiFilter(ResourceVersionResourceIdFilter::class, properties: ['resourceId'])]
#[API\GetCollection(provider: ResourceVersionStateProvider::class)]
#[API\Get(provider: ResourceVersionStateProvider::class)]
class Version
{
    public int $id;

    /**
     * Version number for the resource object.
     */
    public int $version;

    /**
     * The action-type that performed the version change.
     */
    public string $action;

    /**
     * The changes made by the action for this version.
     *
     * @return array<string, mixed>
     */
    public array $changes;

    /**
     * Reconstructed resource data for this version.
     */
    public EmbeddedResource $resource;

    /**
     * Version creation timestamp.
     */
    public \DateTimeInterface $dateCreated;
}
