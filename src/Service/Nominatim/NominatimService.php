<?php

namespace App\Service\Nominatim;

use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NominatimService
{
    public const NOMINATIM_BASE_URI = 'https://nominatim.openstreetmap.org';

    /**
     * Cache calls to Nominatim for 1 hour.
     * This ensures freshness of the data but minimizes bulk-operations hit rates to Nominatim.
     */
    public const NOMINATIM_CACHE_TTL = 3600;

    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private CacheInterface $cache,
    ) {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => self::NOMINATIM_BASE_URI,
            'headers' => ['User-Agent' => 'goteo/v4'],
        ]);
    }

    /**
     * Main request method with built-in caching.
     *
     * @param string $endpoint   One of the available nominatim endpoints
     * @param array{parameters: array}  $options List of parameters to be passed to the endpoint
     *
     * @see https://nominatim.org/release-docs/develop/api/Overview/
     */
    private function request(
        string $endpoint,
        array $options,
    ): array {
        $cacheKey = \sprintf('%s?%s', \ltrim($endpoint, '/'), \http_build_query($options['parameters']));

        return $this->cache->get(
            $cacheKey,
            function (CacheItemInterface $item) use ($endpoint, $options) {
                $item->expiresAfter(self::NOMINATIM_CACHE_TTL);

                $response = $this->httpClient->request('GET', $endpoint, $options);

                return \json_decode($response->getContent(), true);
            }
        );
    }

    /**
     * Get data from the `/search` endpoint.
     *
     * @see https://nominatim.org/release-docs/develop/api/Search/
     */
    public function search(
        string $query,
        int $limit = 1,
        bool $addressDetails = true,
        bool $extraTags = false,
        bool $nameDetails = false,
    ): array {
        return $this->request('/search', [
            'parameters' => [
                'q' => $query,
                'limit' => $limit,
                'addressdetails' => (int) $addressDetails,
                'extratags' => (int) $extraTags,
                'namedetails' => (int) $nameDetails,
                'format' => OutputFormat::Json->value,
            ]
        ]);
    }
}
