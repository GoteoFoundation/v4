<?php

namespace App\Service\Project;

use App\Entity\Project\ProjectTerritory;
use App\Service\Nominatim\NominatimService;

class TerritoryService
{
    public function __construct(
        private NominatimService $nominatimService,
    ) {}

    public function search(string $query): ProjectTerritory
    {
        $search = $this->nominatimService->search($query);

        if (empty($search)) {
            return ProjectTerritory::unknown();
        }

        $result = $search[0];

        if (!\array_key_exists('address', $result)) {
            throw new \Exception("Key 'address' not found in Nominatim result. Did you forget to pass `addressDetails = true`?");
        }

        $address = $result['address'];

        if (!\array_key_exists('country_code', $address)) {
            return ProjectTerritory::unknown();
        }

        return $this->processResultAddress($address);
    }

    private function processResultAddress(array $address): ProjectTerritory
    {
        $country = \strtoupper($address['country_code']);

        $subLvl1 = null;
        $subLvl2 = null;

        foreach ($address as $addrKey => $addrVal) {
            if (!\str_starts_with($addrKey, 'ISO3166-2')) {
                continue;
            }

            $level = \array_reverse(\explode('-', $addrKey))[0];
            $level = \intval(\str_replace('lvl', '', $level));

            if ($level < 5) {
                $subLvl1 = $addrVal;
            } else {
                $subLvl2 = $addrVal;
            }
        }

        return new ProjectTerritory($country, $subLvl1, $subLvl2);
    }
}
