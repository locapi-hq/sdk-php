<?php

declare(strict_types=1);

namespace LocApi\Resources;

class PostalCodeResource extends AbstractResource
{
    /**
     * Search postal codes by postal code prefix and country.
     *
     * @param array{postalCode: string, countryCode: string, limit?: int, offset?: int}|string $postalCodeOrParams
     * @param string|null $countryCode
     * @param array{limit?: int, offset?: int} $options
     * @return array<string, mixed>
     */
    public function search(array|string $postalCodeOrParams, ?string $countryCode = null, array $options = []): array
    {
        if (is_array($postalCodeOrParams)) {
            $params = $postalCodeOrParams;
        } else {
            $params = array_merge([
                'postalCode' => $postalCodeOrParams,
                'countryCode' => $countryCode,
            ], $options);
        }

        return $this->request('GET', '/api/v1/postal-codes', $params);
    }
}
