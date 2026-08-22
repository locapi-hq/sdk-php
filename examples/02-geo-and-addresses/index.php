<?php

declare(strict_types=1);

/**
 * LocAPI PHP SDK - Example 02: Geo Search, Coordinates & Street Addresses
 *
 * This example demonstrates:
 * 1. Geo-radius filtered search (powered by Meilisearch)
 * 2. Nearby locations lookup around GPS coordinates
 * 3. Reverse geocoding (coordinates -> location entity with country enrichment)
 * 4. Location autocomplete with match highlighting
 * 5. Street address search, autocomplete, and address reverse geocoding
 * 6. Administrative boundary retrieval (GeoJSON)
 * 7. Country metadata lookup
 *
 * How to run:
 *   php examples/02-geo-and-addresses/index.php
 *   or: composer example:geo
 */

require_once __DIR__ . '/../bootstrap.php';

$locapi = createLocApiClient();

// Prague Old Town Square coordinates
$pragueCenter = ['lat' => 50.0875, 'lon' => 14.4213];

// ---------------------------------------------------------------------------
// 1. Meilisearch Geo-Radius Search
// ---------------------------------------------------------------------------
echo "1. Geo-radius search: Finding populated places within 15 km of [{$pragueCenter['lat']}, {$pragueCenter['lon']}]...\n";
try {
    $geoResults = $locapi->locations()->searchGeo([
        'lat' => $pragueCenter['lat'],
        'lon' => $pragueCenter['lon'],
        'radiusMeters' => 15000,
        'featureClass' => 'P', // Populated places (cities, towns)
        'limit' => 4,
    ]);

    $items = $geoResults['data']['results'] ?? $geoResults['data'] ?? [];
    echo "✓ Found " . count($items) . " locations within 15 km:\n";
    foreach ($items as $item) {
        $pop = isset($item['population']) && $item['population'] !== null
            ? number_format((float) $item['population'])
            : 'N/A';
        $lat = $item['_geo']['lat'] ?? $item['latitude'] ?? 'N/A';
        $lng = $item['_geo']['lng'] ?? $item['longitude'] ?? 'N/A';

        echo "   • {$item['name']} ({$item['countryCode']}) - Pop: {$pop}\n";
        echo "     Coordinates: [{$lat}, {$lng}]\n";
    }
} catch (Throwable $e) {
    handleExampleException('Geo Search', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 2. Reverse Geocoding
// ---------------------------------------------------------------------------
echo "2. Reverse geocoding coordinates [{$pragueCenter['lat']}, {$pragueCenter['lon']}]...\n";
try {
    $reverse = $locapi->locations()->reverseGeocode([
        'lat' => $pragueCenter['lat'],
        'lon' => $pragueCenter['lon'],
        'radiusMeters' => 5000,
        'enrichCountry' => true,
    ]);

    $data = $reverse['data'] ?? null;
    if ($data) {
        echo "✓ Resolved Location: {$data['name']} ({$data['countryCode']})\n";
        $admin1 = $data['admin1Name'] ?? 'N/A';
        $admin2 = $data['admin2Name'] ?? 'N/A';
        echo "   Admin Division: {$admin1}, {$admin2}\n";
        echo "   Timezone: {$data['timezone']}\n";
        if (!empty($data['country'])) {
            $officialName = $data['country']['officialName'] ?? $data['country']['name'] ?? 'N/A';
            $capital = $data['country']['capital'] ?? 'N/A';
            echo "   Country Info: {$officialName} (Capital: {$capital})\n";
        }
    } else {
        echo "No location found at these coordinates.\n";
    }
} catch (Throwable $e) {
    handleExampleException('Reverse Geocode', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 3. Location Autocomplete (Typeahead)
// ---------------------------------------------------------------------------
echo "3. Real-time Location Autocomplete query: \"Vien\"...\n";
try {
    $autoResults = $locapi->locations()->autocomplete([
        'q' => 'Vien',
        'limit' => 3,
        'highlight' => true,
    ]);

    $suggestions = $autoResults['data'] ?? [];
    echo "✓ Autocomplete suggestions:\n";
    foreach ($suggestions as $match) {
        $feature = ($match['featureClass'] ?? '') . '.' . ($match['featureCode'] ?? '');
        echo "   • {$match['name']} ({$match['countryCode']}) [{$feature}]\n";
    }
} catch (Throwable $e) {
    handleExampleException('Location Autocomplete', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 4. Street Address Search & Autocomplete
// ---------------------------------------------------------------------------
echo "4. Street-level address search: \"Václavské náměstí\"...\n";
try {
    $addressSearch = $locapi->addresses()->search([
        'q' => 'Václavské náměstí',
        'countryCode' => 'CZ',
        'limit' => 3,
    ]);

    $addrList = $addressSearch['data']['results'] ?? $addressSearch['data'] ?? [];
    echo "✓ Found " . count($addrList) . " address records:\n";
    foreach ($addrList as $addr) {
        $formatted = $addr['formattedAddress'] ?? $addr['streetName'] ?? 'Unknown address';
        $postalCode = $addr['postalCode'] ?? 'N/A';
        $municipality = $addr['municipality'] ?? 'N/A';
        $lat = $addr['latitude'] ?? 'N/A';
        $lon = $addr['longitude'] ?? 'N/A';

        echo "   • {$formatted}\n";
        echo "     Postal Code: {$postalCode} | Municipality: {$municipality}\n";
        echo "     Coordinates: [{$lat}, {$lon}]\n";
    }
} catch (Throwable $e) {
    handleExampleException('Address Search', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 5. Address Reverse Geocode
// ---------------------------------------------------------------------------
echo "5. Converting coordinates [{$pragueCenter['lat']}, {$pragueCenter['lon']}] to nearest street address...\n";
try {
    $nearestAddress = $locapi->addresses()->reverseGeocode([
        'lat' => $pragueCenter['lat'],
        'lon' => $pragueCenter['lon'],
        'radius' => 200,
    ]);

    $data = $nearestAddress['data'] ?? null;
    if ($data) {
        $formatted = $data['formattedAddress'] ?? 'N/A';
        $street = trim(($data['streetName'] ?? '') . ' ' . ($data['houseNumber'] ?? ''));
        $distance = isset($data['distanceMeters'])
            ? round((float) $data['distanceMeters']) . ' m'
            : 'N/A';

        echo "✓ Nearest Address: {$formatted}\n";
        echo "   Street: {$street}\n";
        echo "   Distance: {$distance}\n";
    } else {
        echo "No street address found within radius.\n";
    }
} catch (Throwable $e) {
    handleExampleException('Address Reverse Geocode', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 6. Administrative GeoJSON Boundaries
// ---------------------------------------------------------------------------
$boundaryId = 3067696; // Prague
echo "6. Fetching GeoJSON administrative boundary for ID: {$boundaryId}...\n";
try {
    $boundaries = $locapi->locations()->boundaries($boundaryId);
    $geom = $boundaries['data']['geometry'] ?? null;
    if ($geom) {
        $geomType = $geom['type'] ?? 'Unknown';
        $coordsCount = isset($geom['coordinates']) && is_array($geom['coordinates'])
            ? count($geom['coordinates'])
            : 0;
        echo "✓ Boundary Geometry Type: {$geomType}\n";
        echo "   Coordinates Count: {$coordsCount} rings/polygons\n";
    } else {
        echo "Boundary geometry not available for this entity.\n";
    }
} catch (Throwable $e) {
    handleExampleException('Boundaries', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 7. Country Metadata Lookup
// ---------------------------------------------------------------------------
$countryCode = 'CZ';
echo "7. Fetching metadata for country: {$countryCode}...\n";
try {
    $country = $locapi->countries()->getInfo($countryCode);
    $cData = $country['data'] ?? [];
    echo "✓ Country: " . ($cData['officialName'] ?? $cData['commonName'] ?? $countryCode) . "\n";
    echo "   Capital: " . ($cData['capital'] ?? 'N/A') . " | Region: " . ($cData['region'] ?? 'N/A') . "\n";
    echo "   Currencies: " . (is_array($cData['currencies'] ?? null) ? implode(', ', array_keys($cData['currencies'])) : 'N/A') . "\n";
} catch (Throwable $e) {
    handleExampleException('Country Info', $e);
}
