<?php

namespace App\Service\Nominatim;

enum OutputFormat: string
{
    case Json = 'json';
    case JsonV2 = 'jsonv2';
    case GeoJson = 'geojson';
    case GeoCodeJson = 'geocodejson';
}
