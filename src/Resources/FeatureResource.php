<?php

declare(strict_types=1);

namespace LocApi\Resources;

class FeatureResource extends AbstractResource
{
    public function getLocations(string $featureClass, array $params = []): array
    {
        return $this->request('GET', "/v1/features/{$featureClass}/locations", $params);
    }
}
