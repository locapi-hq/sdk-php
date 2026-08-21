<?php

declare(strict_types=1);

namespace LocApi\Resources;

class StatsResource extends AbstractResource
{
    /**
     * Get system performance statistics.
     *
     * @param string|null $period One of: '24h', '7d', '30d', '6m', '1y'
     * @return array<string, mixed>
     */
    public function getPublicStats(?string $period = '24h'): array
    {
        $params = $period !== null ? ['period' => $period] : [];
        return $this->request('GET', '/v1/public/stats', $params);
    }
}
