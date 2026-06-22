<?php

declare(strict_types=1);

namespace LocApi\Resources;

class TimezoneResource extends AbstractResource
{
    public function get(float $lat, float $lon): array
    {
        return $this->request('GET', '/v1/timezones', [
            'lat' => $lat,
            'lon' => $lon
        ]);
    }
}
