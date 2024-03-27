<?php

namespace App\ApiResource;

use ApiPlatform\Metadata as API;
use App\State\VersionStateProvider;
use Gedmo\Loggable\Entity\LogEntry;

#[API\GetCollection(provider: VersionStateProvider::class)]
#[API\Get(provider: VersionStateProvider::class)]
class Version
{
    public function __construct(
        private readonly LogEntry $log,
        private readonly object $entity
    ) {
    }

    public function getId(): ?int
    {
        return $this->log->getId();
    }

    public function getVersion(): ?int
    {
        return $this->log->getVersion();
    }

    public function getAction(): ?string
    {
        return $this->log->getAction();
    }

    public function getResource(): string
    {
        $classPieces = explode('\\', $this->log->getObjectClass());

        return end($classPieces);
    }

    public function getResouceId(): int
    {
        return $this->log->getObjectId();
    }

    public function getResourceData()
    {
        $entity = $this->entity;

        foreach ($this->log->getData() as $changedProperty => $changedValue) {
            $setter = sprintf("set%s", ucfirst($changedProperty));
            $entity->$setter($changedValue);
        }

        return $entity;
    }

    public function getResourceChanges()
    {
        return $this->log->getData();
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->log->getLoggedAt();
    }
}
