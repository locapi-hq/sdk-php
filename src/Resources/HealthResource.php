<?php

declare(strict_types=1);

namespace LocApi\Resources;

class HealthResource extends AbstractResource
{
    /**
     * Liveness probe.
     *
     * @return array<string, mixed>
     */
    public function check(): array
    {
        return $this->request('GET', '/health');
    }

    /**
     * Detailed health check probing backing services (DB, cache, Meilisearch).
     *
     * @return array<string, mixed>
     */
    public function detailed(): array
    {
        return $this->request('GET', '/health/detailed');
    }

    /**
     * Readiness probe verifying application is ready to accept traffic.
     *
     * @return array<string, mixed>
     */
    public function ready(): array
    {
        return $this->request('GET', '/health/ready');
    }
}
