# LocAPI PHP SDK Examples

This directory contains functional, ready-to-run code examples and demonstrations showcasing how to use the official `locapi/sdk` PHP library.

---

## Quick Start

### 1. Configure Environment

Copy the example environment file and set your LocAPI API key (you can obtain or generate keys in the [LocAPI Console](https://locapi.net/console)):

```bash
cp .env.example .env
```

Edit `.env`:
```ini
LOCAPI_API_KEY=your_api_key_here
LOCAPI_BASE_URL=https://locapi.dev
```

> **Note for Local Development:**
> If you are running the Laravel backend locally, set:
> `LOCAPI_BASE_URL=http://127.0.0.1:8000`

---

## Available Examples

### 1. Basic Text Search & Error Handling (`01-basic-search`)
Demonstrates core operations:
- Client initialization with custom options (`apiKey`, `baseUrl`).
- Database full-text location search with country filtering and limits (`$locapi->locations()->search(...)`).
- Single location lookup by GeoNames ID (`$locapi->locations()->get(...)`).
- Multilingual alternative name retrieval (`$locapi->locations()->alternativeNames(...)`).
- Catching and inspecting `LocApiException`, `LocApiAuthenticationException`, and `LocApiRateLimitException`.

**Run:**
```bash
composer example:basic
# or directly:
php examples/01-basic-search/index.php
```

---

### 2. Geo Search, Coordinates & Street Addresses (`02-geo-and-addresses`)
Demonstrates spatial and address features:
- High-performance Meilisearch geo-radius search around GPS coordinates (`$locapi->locations()->searchGeo(...)`).
- Finding nearest locations (`$locapi->locations()->searchNearby(...)`).
- Reverse geocoding GPS coordinates to administrative entities with country enrichment (`$locapi->locations()->reverseGeocode(...)`).
- Real-time location typeahead autocomplete (`$locapi->locations()->autocomplete(...)`).
- Street-level address search (`$locapi->addresses()->search(...)`) and address autocomplete (`$locapi->addresses()->autocomplete(...)`).
- Reverse geocoding to the nearest physical street address (`$locapi->addresses()->reverseGeocode(...)`).
- Retrieving GeoJSON administrative boundaries (`$locapi->locations()->boundaries(...)`).
- Country metadata lookup (`$locapi->countries()->getInfo(...)`).

**Run:**
```bash
composer example:geo
# or directly:
php examples/02-geo-and-addresses/index.php
```

---

### 3. Travel Matrix, Timezones, IP Geolocation & Health (`03-travel-and-ip`)
Demonstrates utility, routing, and network features:
- Multi-origin to multi-destination distance matrix calculation with speed estimation (`$locapi->distanceMatrices()->create(...)`).
- Bulk coordinate reverse lookups in a single request (`$locapi->locations()->bulkLookups(...)`).
- Timezone lookup with daylight saving time (DST) data (`$locapi->timezones()->get(...)`).
- IP address geolocation and WHOIS network allocation (`$locapi->ipAddresses()->getGeolocation(...)`, `$locapi->ipAddresses()->getWhois(...)`).
- Postal code prefix search (`$locapi->postalCodes()->search(...)`).
- Service health diagnostics and latency metrics (`$locapi->health()->detailed()`, `$locapi->stats()->getPublicStats(...)`).

**Run:**
```bash
composer example:travel
# or directly:
php examples/03-travel-and-ip/index.php
```

---

## How to Import in Your Own Project

Install the package and a PSR-18 HTTP client (e.g., Guzzle):
```bash
composer require locapi/sdk guzzlehttp/guzzle guzzlehttp/psr7
```

Initialize and use:
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use LocApi\LocApi;
use LocApi\Exceptions\LocApiException;

$locapi = new LocApi(getenv('LOCAPI_API_KEY'));

try {
    $result = $locapi->locations()->search(['q' => 'Prague', 'limit' => 5]);
    foreach ($result['data'] as $location) {
        echo "- {$location['name']} ({$location['countryCode']})\n";
    }
} catch (LocApiException $e) {
    echo "API Error [{$e->getStatusCode()}]: {$e->getMessage()}\n";
}
```
