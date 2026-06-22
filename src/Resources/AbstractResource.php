<?php

declare(strict_types=1);

namespace LocApi\Resources;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use LocApi\Exceptions\LocApiException;
use LocApi\Exceptions\LocApiAuthenticationException;
use LocApi\Exceptions\LocApiRateLimitException;

abstract class AbstractResource
{
    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $apiKey,
        string $baseUrl
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    protected function request(string $method, string $path, array $queryParams = [], ?array $bodyParams = null): array
    {
        $url = $this->baseUrl . $path;
        if (!empty($queryParams)) {
            // Build query params including correct nested structure for array parameters
            $url .= '?' . http_build_query($queryParams);
        }

        $request = $this->requestFactory->createRequest($method, $url);
        $request = $request
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Accept', 'application/json');

        if ($bodyParams !== null) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $bodyStream = $this->streamFactory->createStream(json_encode($bodyParams));
            $request = $request->withBody($bodyStream);
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Throwable $e) {
            throw new LocApiException('LocAPI Network Error: ' . $e->getMessage(), 0, null, [], [], $e);
        }

        $contents = $response->getBody()->getContents();
        $data = json_decode($contents, true);
        if (!is_array($data)) {
            $data = ['message' => $contents ?: 'Invalid JSON response from server'];
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $data['message'] ?? 'API Request Failed';
            $errorType = $data['errorType'] ?? null;
            $errors = $data['errors'] ?? [];
            $meta = $data['meta'] ?? [];

            if ($statusCode === 401) {
                throw new LocApiAuthenticationException($message, 401, $errorType, $errors, $meta);
            }
            if ($statusCode === 429) {
                throw new LocApiRateLimitException($message, 429, $errorType, $errors, $meta);
            }
            throw new LocApiException($message, $statusCode, $errorType, $errors, $meta);
        }

        return $data;
    }
}
