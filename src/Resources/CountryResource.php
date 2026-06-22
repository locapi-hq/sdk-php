<?php

declare(strict_types=1);

namespace LocApi\Resources;

class CountryResource extends AbstractResource
{
    public function getInfo(string $countryCode): array
    {
        return $this->request('GET', '/v1/countries/info', ['countryCode' => $countryCode]);
    }

    public function getLocations(string $countryCode, array $params = []): array
    {
        return $this->request('GET', "/v1/countries/{$countryCode}/locations", $params);
    }
}
