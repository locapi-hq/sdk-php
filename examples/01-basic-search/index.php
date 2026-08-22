<?php

declare(strict_types=1);

/**
 * LocAPI PHP SDK - Example 01: Basic Search & Error Handling
 *
 * This example demonstrates:
 * 1. Initializing the LocApi client with API key and baseUrl
 * 2. Performing database text searches with filters and pagination
 * 3. Fetching single location details by GeoNames ID
 * 4. Fetching multilingual alternative names
 * 5. Robust error handling using LocApiException hierarchy
 *
 * How to run:
 *   php examples/01-basic-search/index.php
 *   or: composer example:basic
 */

require_once __DIR__ . '/../bootstrap.php';

$locapi = createLocApiClient();

// ---------------------------------------------------------------------------
// 1. Text Search with Country Filter & Limit
// ---------------------------------------------------------------------------
echo "1. Searching for locations matching \"Prague\"...\n";
try {
    $searchResult = $locapi->locations()->search([
        'q' => 'Prague',
        'countryCode' => 'CZ',
        'limit' => 3,
    ]);

    $items = $searchResult['data'] ?? [];
    echo "✓ Found " . count($items) . " results:\n";
    foreach ($items as $item) {
        $pop = isset($item['population']) && $item['population'] !== null
            ? number_format((float) $item['population'])
            : 'N/A';
        $lat = $item['_geo']['lat'] ?? $item['latitude'] ?? 'N/A';
        $lng = $item['_geo']['lng'] ?? $item['longitude'] ?? 'N/A';
        $feature = ($item['featureClass'] ?? '') . '.' . ($item['featureCode'] ?? '');

        echo "   • [{$item['geonameid']}] {$item['name']} ({$item['countryCode']}) - Pop: {$pop}\n";
        echo "     Coordinates: [{$lat}, {$lng}] | Feature: {$feature}\n";
    }
} catch (Throwable $e) {
    handleExampleException('Text Search', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 2. Retrieve Specific Location by ID
// ---------------------------------------------------------------------------
$geonameId = 3067696; // Prague, Czech Republic
echo "2. Fetching location details for GeoNames ID: {$geonameId}...\n";
try {
    $location = $locapi->locations()->get($geonameId);
    $data = $location['data'] ?? [];

    $pop = isset($data['population']) && $data['population'] !== null
        ? number_format((float) $data['population'])
        : 'N/A';
    $lat = $data['_geo']['lat'] ?? $data['latitude'] ?? 'N/A';
    $lng = $data['_geo']['lng'] ?? $data['longitude'] ?? 'N/A';
    $elevation = $data['elevation'] ?? 'N/A';

    echo "✓ Location Name: {$data['name']} ({$data['asciiname']})\n";
    echo "   Country: {$data['countryCode']} | Timezone: {$data['timezone']}\n";
    echo "   Coordinates: lat {$lat}, lon {$lng}\n";
    echo "   Elevation: {$elevation} m | Population: {$pop}\n";
} catch (Throwable $e) {
    handleExampleException('Get Location', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 3. Fetch Multilingual Alternative Names
// ---------------------------------------------------------------------------
echo "3. Fetching Czech alternative names for ID: {$geonameId}...\n";
try {
    $altNames = $locapi->locations()->alternativeNames($geonameId, [
        'lang' => 'cs',
        'limit' => 5,
    ]);

    $records = $altNames['data'] ?? [];
    echo "✓ Found " . count($records) . " alternative name records:\n";
    foreach ($records as $alt) {
        $lang = $alt['isoLanguage'] ?? 'def';
        $preferred = !empty($alt['isPreferredName']) ? 'yes' : 'no';
        echo "   • Language [{$lang}]: \"{$alt['alternateName']}\" (preferred: {$preferred})\n";
    }
} catch (Throwable $e) {
    handleExampleException('Alternative Names', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 4. Demonstrating Error Handling (e.g. Non-existent location)
// ---------------------------------------------------------------------------
$nonExistentId = 999999999;
echo "4. Testing Error Handling (Fetching invalid ID {$nonExistentId})...\n";
try {
    $locapi->locations()->get($nonExistentId);
    echo "Unexpected success!\n";
} catch (Throwable $e) {
    echo "✓ Successfully caught expected exception:\n";
    handleExampleException('Expected Error Demo', $e);
}
