<?php

declare(strict_types=1);

namespace LocApi\Resources;

class LocationResource extends AbstractResource
{
    /**
     * Search locations using standard database full-text search.
     *
     * @param array{q: string, country?: string, countryCode?: string, limit?: int, page?: int} $params
     * @return array<string, mixed>
     */
    public function search(array $params = []): array
    {
        return $this->request('GET', '/v1/locations', $params);
    }

    /**
     * Get a specific location by its GeoNames ID.
     *
     * @param int $geonameid
     * @return array<string, mixed>
     */
    public function get(int $geonameid): array
    {
        return $this->request('GET', "/v1/locations/{$geonameid}");
    }

    /**
     * Search locations using Meilisearch (full-text with optional geo-radius/country/feature filters).
     *
     * @param array{q?: string, lat?: float, lon?: float, latitude?: float, longitude?: float, radius?: int, radiusMeters?: int, countryCode?: string, featureClass?: string, limit?: int, offset?: int, enrichCountry?: bool} $params
     * @return array<string, mixed>
     */
    public function searchGeo(array $params = []): array
    {
        return $this->request('GET', '/v1/locations/geo-search', $params);
    }

    /**
     * Search locations nearby a given coordinate point.
     *
     * @param array{lat?: float, lon?: float, latitude?: float, longitude?: float, radius?: int, radiusMeters?: int, q?: string, limit?: int, enrichCountry?: bool} $params
     * @return array<string, mixed>
     */
    public function searchNearby(array $params): array
    {
        return $this->request('GET', '/v1/locations/nearby', $params);
    }

    /**
     * Reverse geocode a coordinate to the nearest named location.
     *
     * @param array{lat: float, lon: float, radius?: int, radiusMeters?: int, enrichCountry?: bool} $params
     * @return array<string, mixed>
     */
    public function reverseGeocode(array $params): array
    {
        return $this->request('GET', '/v1/locations/reverse-geocode', $params);
    }

    /**
     * Autocomplete / typeahead location search.
     *
     * @param array{q: string, limit?: int, countryCode?: string, featureClass?: string, highlight?: bool} $params
     * @return array<string, mixed>
     */
    public function autocomplete(array $params): array
    {
        return $this->request('GET', '/v1/locations/autocomplete', $params);
    }

    /**
     * Bulk reverse geocode an array of coordinate objects.
     *
     * @param array<int, array{lat: float, lon: float}> $locations
     * @return array<string, mixed>
     */
    public function bulkLookups(array $locations): array
    {
        return $this->request('POST', '/v1/locations/bulk-lookups', [], [
            'locations' => $locations
        ]);
    }

    /**
     * Retrieve GeoJSON administrative boundaries for a location.
     *
     * @param int $geonameid
     * @return array<string, mixed>
     */
    public function boundaries(int $geonameid): array
    {
        return $this->request('GET', "/v1/locations/{$geonameid}/boundaries");
    }

    /**
     * Retrieve multilingual alternative names for a location.
     *
     * @param int $geonameid
     * @param array{lang?: string, includeSpecial?: bool, limit?: int, offset?: int} $params
     * @return array<string, mixed>
     */
    public function alternativeNames(int $geonameid, array $params = []): array
    {
        return $this->request('GET', "/v1/locations/{$geonameid}/alternative-names", $params);
    }
}
