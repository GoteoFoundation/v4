<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata as API;
use AutoMapper\Attribute\MapFrom;
use Doctrine\ORM\Mapping as ORM;

trait MigratedEntity
{
    /**
     * Entity was migrated from the Goteo v3 platform.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column()]
    protected ?bool $migrated = false;

    /**
     * Previous ID of the entity in the Goteo v3 platform.
     */
    #[API\ApiProperty(writable: false)]
    #[MapFrom(if: 'isMigrated')]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $migratedId = null;

    public function isMigrated(): ?bool
    {
        return $this->migrated;
    }

    public function setMigrated(bool $migrated): static
    {
        $this->migrated = $migrated;

        return $this;
    }

    public function getMigratedId(): ?string
    {
        return $this->migratedId;
    }

    public function setMigratedId(string $migratedId): static
    {
        $this->migratedId = $migratedId;

        return $this;
    }
}
