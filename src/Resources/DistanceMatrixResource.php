<?php

declare(strict_types=1);

namespace LocApi\Resources;

class DistanceMatrixResource extends AbstractResource
{
    public function create(array $origins, array $destinations, ?int $speedKmh = null): array
    {
        $body = [
            'origins' => $origins,
            'destinations' => $destinations
        ];

        if ($speedKmh !== null) {
            $body['speedKmh'] = $speedKmh;
        }

        return $this->request('POST', '/v1/distance-matrices', [], $body);
    }
}
