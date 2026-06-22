<?php

declare(strict_types=1);

namespace LocApi\Resources;

class IpAddressResource extends AbstractResource
{
    public function getWhois(string $ip): array
    {
        return $this->request('GET', "/v1/ip-addresses/{$ip}/whois-records");
    }

    public function getGeolocation(string $ip): array
    {
        return $this->request('GET', "/v1/ip-addresses/{$ip}/geolocations");
    }
}
