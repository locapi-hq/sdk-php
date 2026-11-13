<?php

declare(strict_types=1);

namespace LocApi\Resources;

class DistanceMatrixResource extends AbstractResource
{
    /**
     * Compute distance and travel time matrix between origin and destination points.
     *
     * @param array<int, array{lat: float, lon: float}> $origins
     * @param array<int, array{lat: float, lon: float}> $destinations
     * @param int|null $speedKmh Optional speed in km/h (1-300)
     * @param int|null $speedMph Optional speed in mph (1-186)
     * @return array<string, mixed>
     */
    public function create(array $origins, array $destinations, ?int $speedKmh = null, ?int $speedMph = null): array
    {
        $body = [
            'origins' => $origins,
            'destinations' => $destinations,
        ];

        if ($speedKmh !== null) {
            $body['speedKmh'] = $speedKmh;
        } elseif ($speedMph !== null) {
            $body['speedMph'] = $speedMph;
        }

        return $this->request('POST', '/api/v1/distance-matrices', [], $body);
    }
}
