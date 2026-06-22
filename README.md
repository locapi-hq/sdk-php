# LocAPI SDK for PHP

The official PHP client library for **LocAPI** — a modern, high-performance geolocation and geosearch service.

This SDK is Decoupled, conforming to the **PSR-18 (HTTP Client)** and **PSR-17 (HTTP Factory)** specifications. It uses auto-discovery to detect your installed HTTP client (like Guzzle or Symfony HTTP Client), meaning it has zero package lock-in.

---

## Requirements

- PHP `8.2` or higher.
- A PSR-18 HTTP Client (e.g. `guzzlehttp/guzzle` or `symfony/http-client`).
- A PSR-17 HTTP Factory (e.g. `guzzlehttp/psr7` or `nyholm/psr7`).

---

## Installation

Install the package via Composer. If your project does not already have an HTTP client installed, Composer will suggest installing Guzzle.

```bash
composer require locapi/sdk

# Recommended: If you need an HTTP client and factories, install Guzzle:
composer require guzzlehttp/guzzle guzzlehttp/psr7
```

---

## Initialization

The SDK uses `php-http/discovery` to automatically detect your installed HTTP client and message factories.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use LocApi\LocApi;

// 1. Basic initialization (auto-detects HTTP client and factories)
$locapi = new LocApi('your_api_key_here');

// 2. Custom Base URL (e.g., self-hosted or local testing environment)
$locapi = new LocApi('your_api_key_here', 'http://localhost:8000');

// 3. Advanced: Explicitly inject custom PSR-18 client & factories
$locapi = new LocApi(
    apiKey: 'your_api_key_here',
    httpClient: $myCustomPsr18Client,
    requestFactory: $myPsr17RequestFactory,
    streamFactory: $myPsr17StreamFactory
);
```

---

## Code Examples

### 1. Location Search (Database Full-Text)
Query places by name:

```php
<?php

use LocApi\Exceptions\LocApiException;

try {
    $response = $locapi->locations()->search([
        'q' => 'Prague',
        'limit' => 5
    ]);

    foreach ($response['data'] as $location) {
        printf("- %s (%s): Population: %d\n", 
            $location['name'], 
            $location['countryCode'], 
            $location['population'] ?? 0
        );
    }
} catch (LocApiException $e) {
    echo "API Error: " . $e->getMessage();
}
```

### 2. High-Performance Geo-Search (Meilisearch)
Search using geo coordinates:

```php
$response = $locapi->locations()->searchGeo([
    'lat' => 50.0755,
    'lon' => 14.4378,
    'radiusMeters' => 10000, // 10km radius
    'limit' => 10
]);
```

### 3. Distance Matrix calculation
Compute Travel distances and durations between origins and destinations:

```php
$origins = [['lat' => 50.0755, 'lon' => 14.4378]];
$destinations = [['lat' => 49.1951, 'lon' => 16.6068]];

$matrix = $locapi->distanceMatrices()->create($origins, $destinations, 90);
```

### 4. Timezone Lookup
Resolve timezone details for a coordinate:

```php
$timezone = $locapi->timezones()->get(50.0755, 14.4378);
echo $timezone['data']['timezone']; // "Europe/Prague"
```

---

## Error Handling

All client methods throw exceptions extending `LocApi\Exceptions\LocApiException`.

```php
<?php

use LocApi\Exceptions\LocApiAuthenticationException;
use LocApi\Exceptions\LocApiRateLimitException;
use LocApi\Exceptions\LocApiException;

try {
    $locapi->locations()->get(-1);
} catch (LocApiAuthenticationException $e) {
    // Thrown on 401 response
    echo "Invalid API Key: " . $e->getMessage();
} catch (LocApiRateLimitException $e) {
    // Thrown on 429 response (quota exceeded or limit hit)
    echo "Rate limit exceeded: " . $e->getMessage();
} catch (LocApiException $e) {
    // Thrown on other API failures (e.g., 404, 500)
    echo "API Error (" . $e->getStatusCode() . "): " . $e->getMessage();
    print_r($e->getErrors()); // Validation issues list
}
```
