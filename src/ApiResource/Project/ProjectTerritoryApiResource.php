<?php

namespace App\ApiResource\Project;

use App\Validator\CountrySubdivision;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ISO 3166 data about the Project's territory of interest.
 */
class ProjectTerritoryApiResource
{
    /**
     * ISO 3166-1 alpha-2 two-letter country code.
     */
    #[Assert\NotBlank()]
    #[Assert\Country(alpha3: false)]
    public string $country;

    /**
     * ISO 3166-2 first level subdivision code.\
     * e.g: ES-AN (Andalucía, Spain).
     */
    #[CountrySubdivision()]
    public string $subLvl1;

    /**
     * ISO 3166-2 second level subdivision code.\
     * e.g: ES-GR (Granada, Andalucía, Spain).
     */
    #[CountrySubdivision()]
    public string $subLvl2;
}
