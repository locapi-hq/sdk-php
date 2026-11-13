# LocAPI SDK for PHP

The official PHP client library for **LocAPI** — a modern, high-performance geolocation and geosearch service.

This SDK is decoupled, conforming to the **PSR-18 (HTTP Client)** and **PSR-17 (HTTP Factory)** specifications. It uses auto-discovery to detect your installed HTTP client (like Guzzle or Symfony HTTP Client), meaning it has zero package lock-in.

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

// 2. Custom origin (self-hosted or local). The client appends /api/v1 and /api/health.
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
Search using geo coordinates and filters:

```php
$response = $locapi->locations()->searchGeo([
    'lat' => 50.0755,
    'lon' => 14.4378,
    'radiusMeters' => 10000, // 10km radius
    'limit' => 10
]);
```

### 3. Street-Level Address Search & Autocomplete
Search physical addresses and auto-suggest:

```php
// Full-text address search
$addresses = $locapi->addresses()->search([
    'q' => 'Václavské náměstí',
    'countryCode' => 'CZ',
    'limit' => 5
]);

// Real-time address autocomplete
$suggestions = $locapi->addresses()->autocomplete([
    'q' => 'Václav',
    'limit' => 5,
    'highlight' => true
]);

// Reverse geocode to nearest address
$nearestAddress = $locapi->addresses()->reverseGeocode(50.0813, 14.4267, 300);
```

### 4. Bulk Reverse Geocoding
Resolve nearest named locations for an array of coordinate points in a single call:

```php
$results = $locapi->locations()->bulkLookups([
    ['lat' => 50.0755, 'lon' => 14.4378],
    ['lat' => 48.8566, 'lon' => 2.3522],
]);
```

### 5. Postal Code Lookup
Search postal codes:

```php
$postalCodes = $locapi->postalCodes()->search('11000', 'CZ');
```

### 6. Distance Matrix Calculation
Compute travel distances and durations between origins and destinations:

```php
$origins = [['lat' => 50.0755, 'lon' => 14.4378]];
$destinations = [['lat' => 49.1951, 'lon' => 16.6068]];

$matrix = $locapi->distanceMatrices()->create($origins, $destinations, speedKmh: 90);
```

### 7. Timezone Lookup
Resolve timezone details for a coordinate:

```php
$timezone = $locapi->timezones()->get(50.0755, 14.4378);
echo $timezone['data']['timezone']; // "Europe/Prague"
```

### 8. Health & System Statistics
Monitor backend health and retrieve public performance statistics:

```php
$health = $locapi->health()->detailed();
$stats = $locapi->stats()->getPublicStats('7d');
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

---

## Runnable Examples

The package includes ready-to-run examples in the [`examples/`](./examples) directory:

- **`01-basic-search`**: Basic search, single lookup by ID, alternative names, and exception handling.
- **`02-geo-and-addresses`**: Meilisearch geo-radius, nearby, reverse geocode, autocomplete, street address search, and GeoJSON boundaries.
- **`03-travel-and-ip`**: Distance matrix, bulk coordinate lookups, timezone lookup, IP geolocation, WHOIS, postal codes, and health metrics.

### Running Examples

1. Copy and configure the environment:
   ```bash
   cp examples/.env.example examples/.env
   # Edit examples/.env with your LOCAPI_API_KEY
   ```
2. Run any example using Composer scripts:
   ```bash
   composer example:basic
   composer example:geo
   composer example:travel
   ```
   Or directly via PHP CLI:
   ```bash
   php examples/01-basic-search/index.php
   ```

See the [Examples README](./examples/README.md) for full details.

