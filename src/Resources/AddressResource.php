<?php

declare(strict_types=1);

namespace LocApi\Resources;

class AddressResource extends AbstractResource
{
    /**
     * Search street-level addresses.
     *
     * @param array{q: string, countryCode?: string, postalCode?: string, municipality?: string, limit?: int, offset?: int} $params
     * @return array<string, mixed>
     */
    public function search(array $params): array
    {
        return $this->request('GET', '/api/v1/addresses', $params);
    }

    /**
     * Autocomplete / typeahead suggestions for addresses.
     *
     * @param array{q: string, limit?: int, countryCode?: string, highlight?: bool} $params
     * @return array<string, mixed>
     */
    public function autocomplete(array $params): array
    {
        return $this->request('GET', '/api/v1/addresses/autocomplete', $params);
    }

    /**
     * Reverse geocode a coordinate to the nearest physical address.
     *
     * @param array{lat: float, lon: float, radius?: int}|float $latOrParams
     * @param float|null $lon
     * @param int|null $radius
     * @return array<string, mixed>
     */
    public function reverseGeocode(array|float $latOrParams, ?float $lon = null, ?int $radius = null): array
    {
        if (is_array($latOrParams)) {
            $params = $latOrParams;
        } else {
            $params = [
                'lat' => $latOrParams,
                'lon' => $lon,
            ];
            if ($radius !== null) {
                $params['radius'] = $radius;
            }
        }

        return $this->request('GET', '/api/v1/addresses/reverse-geocode', $params);
    }

    /**
     * Get a specific address by its numeric ID.
     *
     * @param int $id
     * @return array<string, mixed>
     */
    public function get(int $id): array
    {
        return $this->request('GET', "/api/v1/addresses/{$id}");
    }
}
