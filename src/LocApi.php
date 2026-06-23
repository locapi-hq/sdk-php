<?php

declare(strict_types=1);

namespace LocApi;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use LocApi\Resources\LocationResource;
use LocApi\Resources\CountryResource;
use LocApi\Resources\FeatureResource;
use LocApi\Resources\PostalCodeResource;
use LocApi\Resources\DistanceMatrixResource;
use LocApi\Resources\TimezoneResource;
use LocApi\Resources\IpAddressResource;

class LocApi
{
    private string $apiKey;
    private string $baseUrl;
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    // Cache of instantiated resource services
    private array $resources = [];

    public function __construct(
        string $apiKey,
        ?string $baseUrl = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl ?: 'https://locapi.dev';
        
        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
    }

    public function locations(): LocationResource
    {
        return $this->getResource(LocationResource::class);
    }

    public function countries(): CountryResource
    {
        return $this->getResource(CountryResource::class);
    }

    public function features(): FeatureResource
    {
        return $this->getResource(FeatureResource::class);
    }

    public function postalCodes(): PostalCodeResource
    {
        return $this->getResource(PostalCodeResource::class);
    }

    public function distanceMatrices(): DistanceMatrixResource
    {
        return $this->getResource(DistanceMatrixResource::class);
    }

    public function timezones(): TimezoneResource
    {
        return $this->getResource(TimezoneResource::class);
    }

    public function ipAddresses(): IpAddressResource
    {
        return $this->getResource(IpAddressResource::class);
    }

    /**
     * Helper to load or construct cached resource endpoints.
     */
    private function getResource(string $className)
    {
        if (!isset($this->resources[$className])) {
            $this->resources[$className] = new $className(
                $this->httpClient,
                $this->requestFactory,
                $this->streamFactory,
                $this->apiKey,
                $this->baseUrl
            );
        }

        return $this->resources[$className];
    }
}
