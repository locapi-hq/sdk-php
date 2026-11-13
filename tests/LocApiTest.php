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

    private function createSdk(string $apiKey = 'test_key', string $baseUrl = 'http://localhost:8000'): LocApi
    {
        return new LocApi(
            $apiKey,
            $baseUrl,
            $this->mockClient,
            $this->mockRequestFactory,
            $this->mockStreamFactory
        );
    }

    public function testClientUsesCustomBaseUrlAndInjectsHeader()
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);

        $this->mockRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'http://localhost:8000/api/v1/locations?q=Prague')
            ->willReturn($mockRequest);

        $mockRequest->expects($this->exactly(2))
            ->method('withHeader')
            ->willReturnMap([
                ['Locapi-Api-Key', 'test_key', $mockRequest],
                ['Accept', 'application/json', $mockRequest]
            ]);

        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockStream->method('getContents')->willReturn(json_encode(['success' => true, 'data' => []]));

        $this->mockClient->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $sdk = $this->createSdk();
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

        $sdk = $this->createSdk('bad_key');

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

        $sdk = $this->createSdk();

        $this->expectException(LocApiRateLimitException::class);
        $this->expectExceptionCode(429);

        $sdk->locations()->search(['q' => 'Prague']);
    }

    public function testBulkLookupsSendsCoordinatesPayload()
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockBodyStream = $this->createMock(StreamInterface::class);

        $coords = [
            ['lat' => 50.08, 'lon' => 14.43],
            ['lat' => 48.85, 'lon' => 2.35]
        ];

        $this->mockRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'http://localhost:8000/api/v1/locations/bulk-lookups')
            ->willReturn($mockRequest);

        $this->mockStreamFactory->expects($this->once())
            ->method('createStream')
            ->with(json_encode(['locations' => $coords]))
            ->willReturn($mockBodyStream);

        $mockRequest->method('withHeader')->willReturn($mockRequest);
        $mockRequest->expects($this->once())
            ->method('withBody')
            ->with($mockBodyStream)
            ->willReturn($mockRequest);

        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockStream->method('getContents')->willReturn(json_encode(['success' => true, 'data' => []]));

        $this->mockClient->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $sdk = $this->createSdk();
        $response = $sdk->locations()->bulkLookups($coords);
        $this->assertTrue($response['success']);
    }

    public function testAddressesResourceEndpoints()
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);

        $this->mockRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'http://localhost:8000/api/v1/addresses?q=Main+Street&countryCode=CZ')
            ->willReturn($mockRequest);

        $mockRequest->method('withHeader')->willReturn($mockRequest);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockStream->method('getContents')->willReturn(json_encode(['success' => true, 'data' => []]));

        $this->mockClient->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $sdk = $this->createSdk();
        $response = $sdk->addresses()->search(['q' => 'Main Street', 'countryCode' => 'CZ']);
        $this->assertTrue($response['success']);
    }

    public function testPostalCodesSearchWithPositionalArguments()
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);

        $this->mockRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'http://localhost:8000/api/v1/postal-codes?postalCode=11000&countryCode=CZ&limit=10')
            ->willReturn($mockRequest);

        $mockRequest->method('withHeader')->willReturn($mockRequest);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockStream->method('getContents')->willReturn(json_encode(['success' => true, 'data' => []]));

        $this->mockClient->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $sdk = $this->createSdk();
        $response = $sdk->postalCodes()->search('11000', 'CZ', ['limit' => 10]);
        $this->assertTrue($response['success']);
    }

    public function testHealthAndStatsEndpoints()
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);

        $this->mockRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'http://localhost:8000/api/health/detailed')
            ->willReturn($mockRequest);

        $mockRequest->method('withHeader')->willReturn($mockRequest);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockStream->method('getContents')->willReturn(json_encode(['status' => 'ok']));

        $this->mockClient->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $sdk = $this->createSdk();
        $response = $sdk->health()->detailed();
        $this->assertSame('ok', $response['status']);
    }
}
