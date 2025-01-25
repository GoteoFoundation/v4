<?php

namespace App\Entity\Project;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents the territory of interest for a Project.
 */
#[ORM\Embeddable]
class ProjectTerritory
{
    #[ORM\Column(type: Types::STRING, nullable: false)]
    public readonly string $country;

    /**
     * First-level sub-division of the country.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public readonly ?string $subLvl1;

    /**
     * Second-level sub-division of the country.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public readonly ?string $subLvl2;

    public function __construct(
        string $country,
        ?string $subLvl1 = null,
        ?string $subLvl2 = null,
    ) {
        $this->country = $country;
        $this->subLvl1 = $subLvl1 ?? null;
        $this->subLvl2 = $subLvl2 ?? null;
    }
}
