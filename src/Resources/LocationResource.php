<?php

declare(strict_types=1);

namespace LocApi\Resources;

class LocationResource extends AbstractResource
{
    public function search(array $params = []): array
    {
        return $this->request('GET', '/v1/locations', $params);
    }

    public function get(int $geonameid): array
    {
        return $this->request('GET', "/v1/locations/{$geonameid}");
    }

    public function searchGeo(array $params = []): array
    {
        return $this->request('GET', '/v1/locations/geo-search', $params);
    }

    public function searchNearby(array $params): array
    {
        return $this->request('GET', '/v1/locations/nearby', $params);
    }

    public function reverseGeocode(array $params): array
    {
        return $this->request('GET', '/v1/locations/reverse-geocode', $params);
    }

    public function autocomplete(array $params): array
    {
        return $this->request('GET', '/v1/locations/autocomplete', $params);
    }

    public function bulkLookups(array $geonameids): array
    {
        return $this->request('POST', '/v1/locations/bulk-lookups', [], [
            'geonameids' => $geonameids
        ]);
    }

    public function boundaries(int $geonameid): array
    {
        return $this->request('GET', "/v1/locations/{$geonameid}/boundaries");
    }

    public function alternativeNames(int $geonameid): array
    {
        return $this->request('GET', "/v1/locations/{$geonameid}/alternative-names");
    }
}
