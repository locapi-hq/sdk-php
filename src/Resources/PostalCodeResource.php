<?php

declare(strict_types=1);

namespace LocApi\Resources;

class PostalCodeResource extends AbstractResource
{
    public function search(array $params): array
    {
        return $this->request('GET', '/v1/postal-codes', $params);
    }
}
