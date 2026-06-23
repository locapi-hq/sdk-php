<?php

declare(strict_types=1);

namespace LocApi\Tests;

use PHPUnit\Framework\TestCase;
use LocApi\LocApi;
use LocApi\Exceptions\LocApiAuthenticationException;
use LocApi\Exceptions\LocApiRateLimitException;
use LocApi\Exceptions\LocApiException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class LocApiTest extends TestCase
{
    private $mockClient;
    private $mockRequestFactory;
    private $mockStreamFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockClient = $this->createMock(ClientInterface::class);
        $this->mockRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->mockStreamFactory = $this->createMock(StreamFactoryInterface::class);
    }

    public function testClientUsesCustomBaseUrlAndInjectsBearerHeader()
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);

        // Expect request building with custom base URL
        $this->mockRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'http://localhost:8000/v1/locations?q=Prague')
            ->willReturn($mockRequest);

        // Expect headers injection
        $mockRequest->expects($this->exactly(2))
            ->method('withHeader')
            ->willReturnMap([
                ['Locapi-Api-Key', 'test_key', $mockRequest],
                ['Accept', 'application/json', $mockRequest]
            ]);

        // Expect response parsing
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockStream->method('getContents')->willReturn(json_encode(['success' => true, 'data' => []]));

        $this->mockClient->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $sdk = new LocApi(
            'test_key',
            'http://localhost:8000',
            $this->mockClient,
            $this->mockRequestFactory,
            $this->mockStreamFactory
        );

        $response = $sdk->locations()->search(['q' => 'Prague']);
        $this->assertTrue($response['success']);
    }

    public function testThrowsAuthenticationExceptionOn401()
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);

        $this->mockRequestFactory->method('createRequest')->willReturn($mockRequest);
        $mockRequest->method('withHeader')->willReturn($mockRequest);

        $mockResponse->method('getStatusCode')->willReturn(401);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockStream->method('getContents')->willReturn(json_encode([
            'message' => 'Unauthorized key',
            'errorType' => 'UNAUTHORIZED'
        ]));

        $this->mockClient->method('sendRequest')->willReturn($mockResponse);

        $sdk = new LocApi(
            'bad_key',
            'https://locapi.dev',
            $this->mockClient,
            $this->mockRequestFactory,
            $this->mockStreamFactory
        );

        $this->expectException(LocApiAuthenticationException::class);
        $this->expectExceptionCode(401);
        
        $sdk->locations()->get(123);
    }

    public function testThrowsRateLimitExceptionOn429()
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);

        $this->mockRequestFactory->method('createRequest')->willReturn($mockRequest);
        $mockRequest->method('withHeader')->willReturn($mockRequest);

        $mockResponse->method('getStatusCode')->willReturn(429);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockStream->method('getContents')->willReturn(json_encode([
            'message' => 'Too many requests',
            'errorType' => 'RATE_LIMIT_EXCEEDED'
        ]));

        $this->mockClient->method('sendRequest')->willReturn($mockResponse);

        $sdk = new LocApi(
            'test_key',
            'https://locapi.dev',
            $this->mockClient,
            $this->mockRequestFactory,
            $this->mockStreamFactory
        );

        $this->expectException(LocApiRateLimitException::class);
        $this->expectExceptionCode(429);

        $sdk->locations()->search(['q' => 'Prague']);
    }
}
