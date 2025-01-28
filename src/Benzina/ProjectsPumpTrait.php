<?php

namespace App\Benzina;

trait ProjectsPumpTrait
{
    /**
     * Normalizes flexible address strings to obtain highly cacheable and improved search queries.
     * Based on analysis of the Goteo v3 `project.project_location` values.
     *
     * @param int $detailLevel Desired number of remaining components in output address
     */
    public static function cleanProjectLocation(string $location, int $detailLevel = 3): string
    {
        // Skip web addresses
        if (
            \str_starts_with($location, 'www.')
            || \str_starts_with($location, 'http://')
            || \str_starts_with($location, 'https://')
        ) {
            return '';
        }

        // Remove secondary conjoined places from locations
        // e.g: "España y el mundo" -> "España"
        foreach ([' / ', ' | ', ' - ', ' y ', ' and ', ' & '] as $conjoinment) {
            if (\str_contains($location, $conjoinment)) {
                $location = \explode($conjoinment, $location)[0];
            }
        }

        // Normalize parenthesis
        if (\str_contains($location, '(') || \str_contains($location, ')')) {
            $location = \str_replace('(', ',', $location);
            $location = \str_replace(')', '', $location);
        }

        // Remove colon specifications
        // e.g: "Universidad Carlos III de Madrid: Campus de Getafe, Calle Madrid, Getafe, España" -> "Campus de Getafe, Calle Madrid, Getafe, España"
        $location = \preg_replace('/^[\w ]+:/', '', $location);

        // Process comma-separated address pieces
        $location = \explode(',', $location);
        $location = \array_map(function ($l) {
            $l = trim($l);

            // Normalize typos and name variations
            foreach (self::COMMON_VARIATIONS as $standard => $variations) {
                if (in_array(\mb_strtoupper($l), $variations)) {
                    $l = $standard;
                }
            }

            return $l;
        }, $location);

        // Clean non desired location pieces
        $location = \array_filter($location, function ($l) {
            if (empty($l)) {
                return false;
            }

            // Skip numeric only pieces: coordinates, street numbers, etc
            if (\preg_match('/^[-\d.]*$/', $l) || \str_contains($l, 'º')) {
                return false;
            }

            return true;
        });

        $location = \join(', ', \array_slice($location, -1 * $detailLevel));

        // Trim remaining numbers and punctuation marks
        $location = \preg_replace('/^[\d\.\-;]+/', '', $location);
        $location = \preg_replace('/[\d\.\-;]+$/', '', $location);

        return \mb_strtoupper(\trim($location));
    }

    /**
     * @var array<string, array> The standard preferred name and a list of possible variations and misspellings
     */
    private const COMMON_VARIATIONS = [
        'ESPAÑA' => ['ESPANYA', 'ESPANHA', 'ESPAGNE', 'SPAGNA', 'SPANIEN', 'SPAIN'],
        'CATALUÑA' => ['CATALUNYA', 'PAÏSOS CATALANS'],
        'ANDALUCÍA' => ['ANDALUCIA', 'ANDALUSIA'],
        'FRANCIA' => ['FRANCE'],
        'ITALIA' => ['ITALY'],
        'ALEMANIA' => ['GERMANY'],
        'CÁDIZ' => ['CADIZ'],
        'VALENCIA' => ['VALÈNCIA', 'PROVINCIA DE VALENCIA'],
        'ALICANTE' => ['ALACANT'],
        'CÁCERES' => ['CACERES'],
        'BALEARES' => ['BALEAREN', 'ISLAS BALEARES', 'ILLES BALEARS', 'BALEARIC ISLANDS'],
        'GALICIA' => ['GALIZA'],
        'BILBAO' => ['BILBO'],
        'LLEIDA' => ['LÉRIDA'],
        'BARCELONA' => ['PROVINCIA DE BARCELONA'],
        'MURCIA' => ['REGIÓN DE MURCIA'],
    ];

    private const PROJECT_KEYS = [
        'id',
        'name',
        'subtitle',
        'lang',
        'currency',
        'currency_rate',
        'status',
        'translate',
        'progress',
        'owner',
        'node',
        'amount',
        'mincost',
        'maxcost',
        'days',
        'num_investors',
        'popularity',
        'num_messengers',
        'num_posts',
        'created',
        'updated',
        'published',
        'success',
        'closed',
        'passed',
        'contract_name',
        'contract_nif',
        'phone',
        'contract_email',
        'address',
        'zipcode',
        'location',
        'country',
        'image',
        'description',
        'motivation',
        'video',
        'video_usubs',
        'about',
        'goal',
        'related',
        'spread',
        'reward',
        'category',
        'keywords',
        'media',
        'media_usubs',
        'currently',
        'project_location',
        'scope',
        'resource',
        'comment',
        'contract_entity',
        'contract_birthdate',
        'entity_office',
        'entity_name',
        'entity_cif',
        'post_address',
        'secondary_address',
        'post_zipcode',
        'post_location',
        'post_country',
        'amount_users',
        'amount_call',
        'maxproj',
        'analytics_id',
        'facebook_pixel',
        'social_commitment',
        'social_commitment_description',
        'execution_plan',
        'sustainability_model',
        'execution_plan_url',
        'sustainability_model_url',
        'sign_url',
        'sign_url_action',
    ];
}
