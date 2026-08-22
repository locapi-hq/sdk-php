<?php

declare(strict_types=1);

/**
 * LocAPI PHP SDK - Example 03: Distance Matrix, Timezones, IP Geolocation & System Stats
 *
 * This example demonstrates:
 * 1. Computing distance matrices and travel times between multiple GPS coordinates
 * 2. Bulk reverse coordinate lookups in a single request
 * 3. Timezone resolution and DST transition inspection
 * 4. IP address geolocation and WHOIS record lookup
 * 5. Postal code search
 * 6. Checking API cluster health and public latency metrics
 *
 * How to run:
 *   php examples/03-travel-and-ip/index.php
 *   or: composer example:travel
 */

require_once __DIR__ . '/../bootstrap.php';

$locapi = createLocApiClient();

// ---------------------------------------------------------------------------
// 1. Distance Matrix (Origins -> Destinations)
// ---------------------------------------------------------------------------
echo "1. Computing Distance Matrix for Central European Capitals...\n";
try {
    $origins = [
        ['lat' => 50.0755, 'lon' => 14.4378], // Prague
        ['lat' => 48.1486, 'lon' => 17.1077], // Bratislava
    ];
    $destinations = [
        ['lat' => 48.2082, 'lon' => 16.3738], // Vienna
        ['lat' => 47.4979, 'lon' => 19.0402], // Budapest
    ];

    $matrix = $locapi->distanceMatrices()->create(
        origins: $origins,
        destinations: $destinations,
        speedKmh: 90 // Average driving speed for travel time estimation
    );

    $originCount = count($matrix['data']['origins'] ?? $origins);
    $destCount = count($matrix['data']['destinations'] ?? $destinations);
    echo "✓ Distance Matrix computed ({$originCount}x{$destCount}):\n";

    $originLabels = ['Prague', 'Bratislava'];
    $destLabels = ['Vienna', 'Budapest'];

    $matrixRows = $matrix['data']['matrix'] ?? [];
    foreach ($matrixRows as $originIdx => $row) {
        $originName = $originLabels[$originIdx] ?? "Origin #{$originIdx}";
        echo "   From {$originName}:\n";
        foreach ($row as $destIdx => $cell) {
            $destName = $destLabels[$destIdx] ?? "Dest #{$destIdx}";
            $distKm = isset($cell['distanceKm']) ? round((float) $cell['distanceKm']) : 'N/A';
            $durationHrs = isset($cell['durationMinutes'])
                ? number_format(((float) $cell['durationMinutes']) / 60, 1)
                : 'N/A';
            echo "     -> {$destName}: {$distKm} km (~{$durationHrs} h)\n";
        }
    }
} catch (Throwable $e) {
    handleExampleException('Distance Matrix', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 2. Bulk Reverse Coordinate Lookups
// ---------------------------------------------------------------------------
echo "2. Performing bulk lookup for coordinate points...\n";
try {
    $bulkPoints = [
        ['lat' => 50.0755, 'lon' => 14.4378], // Prague
        ['lat' => 48.8566, 'lon' => 2.3522],  // Paris
        ['lat' => 52.5200, 'lon' => 13.4050], // Berlin
    ];

    $bulkResult = $locapi->locations()->bulkLookups($bulkPoints);
    $resolved = $bulkResult['data'] ?? [];
    echo "✓ Bulk lookup resolved " . count($resolved) . " locations:\n";
    foreach ($resolved as $idx => $loc) {
        $name = $loc['name'] ?? $loc['asciiname'] ?? 'Unknown';
        $country = $loc['countryCode'] ?? 'N/A';
        echo "   • Point #{$idx}: {$name} ({$country})\n";
    }
} catch (Throwable $e) {
    handleExampleException('Bulk Lookups', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 3. Timezone Resolution
// ---------------------------------------------------------------------------
$tokyoCoord = ['lat' => 35.6762, 'lon' => 139.6503];
echo "3. Resolving Timezone for Tokyo [{$tokyoCoord['lat']}, {$tokyoCoord['lon']}]...\n";
try {
    $tz = $locapi->timezones()->get($tokyoCoord['lat'], $tokyoCoord['lon']);
    $data = $tz['data'] ?? [];

    $tzId = $data['timezone'] ?? 'N/A';
    $gmtOffset = $data['gmtOffset'] ?? 'N/A';
    $rawOffset = $data['rawOffset'] ?? 'N/A';
    $localTime = $data['localTime'] ?? 'N/A';
    $hasDst = !empty($data['dstOffset']) ? 'Yes' : 'No';

    echo "✓ Timezone ID: {$tzId}\n";
    echo "   UTC Offset: {$gmtOffset} hours (raw: {$rawOffset})\n";
    echo "   Current Local Time: {$localTime}\n";
    echo "   Has DST: {$hasDst}\n";
} catch (Throwable $e) {
    handleExampleException('Timezone Lookup', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 4. IP Address Geolocation & WHOIS
// ---------------------------------------------------------------------------
$testIp = '8.8.8.8'; // Google Public DNS
echo "4. Looking up Geolocation & WHOIS for IP: {$testIp}...\n";
try {
    $ipGeo = $locapi->ipAddresses()->getGeolocation($testIp);
    $geoData = $ipGeo['data'] ?? [];

    $country = ($geoData['countryName'] ?? '') . ' (' . ($geoData['countryCode'] ?? '') . ')';
    $city = $geoData['city'] ?? 'N/A';
    $isp = ($geoData['isp'] ?? 'N/A') . ' (' . ($geoData['asn'] ?? 'N/A') . ')';
    $coords = ($geoData['latitude'] ?? 'N/A') . ', ' . ($geoData['longitude'] ?? 'N/A');

    echo "✓ IP Geolocation:\n";
    echo "   Country: {$country}\n";
    echo "   City: {$city}\n";
    echo "   ISP/ASN: {$isp}\n";
    echo "   Coordinates: [{$coords}]\n";

    $ipWhois = $locapi->ipAddresses()->getWhois($testIp);
    $whoisData = $ipWhois['data'] ?? [];
    echo "✓ IP WHOIS Allocation:\n";
    echo "   Net Range: " . ($whoisData['netRange'] ?? 'N/A') . "\n";
    echo "   Org: " . ($whoisData['orgName'] ?? 'N/A') . "\n";
} catch (Throwable $e) {
    handleExampleException('IP Services', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 5. Postal Code Search
// ---------------------------------------------------------------------------
echo "5. Searching Postal Codes in Czech Republic prefix \"110\"...\n";
try {
    $postalResults = $locapi->postalCodes()->search('110', 'CZ', ['limit' => 3]);
    $postalData = $postalResults['data'] ?? [];
    echo "✓ Found " . count($postalData) . " postal codes:\n";
    foreach ($postalData as $p) {
        $code = $p['postalCode'] ?? 'N/A';
        $place = $p['placeName'] ?? 'N/A';
        $admin = $p['adminName1'] ?? 'N/A';
        echo "   • {$code} - {$place} ({$admin})\n";
    }
} catch (Throwable $e) {
    handleExampleException('Postal Code Search', $e);
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// ---------------------------------------------------------------------------
// 6. API Health & Public Stats
// ---------------------------------------------------------------------------
echo "6. Checking API Health & System Metrics...\n";
try {
    $health = $locapi->health()->detailed();
    echo "✓ Service Status: " . ($health['status'] ?? 'ok') . "\n";
    if (isset($health['services'])) {
        $db = $health['services']['database'] ?? [];
        $meili = $health['services']['meilisearch'] ?? [];
        $cache = $health['services']['cache'] ?? [];
        echo "   Database Latency: " . ($db['latencyMs'] ?? 'N/A') . " ms (" . ($db['status'] ?? 'unknown') . ")\n";
        echo "   Meilisearch Latency: " . ($meili['latencyMs'] ?? 'N/A') . " ms (" . ($meili['status'] ?? 'unknown') . ")\n";
        echo "   Redis Cache Latency: " . ($cache['latencyMs'] ?? 'N/A') . " ms (" . ($cache['status'] ?? 'unknown') . ")\n";
    }

    $stats = $locapi->stats()->getPublicStats('24h');
    $statsData = $stats['data'] ?? [];
    $totalReq = isset($statsData['totalRequests']) && $statsData['totalRequests'] !== null
        ? number_format((float) $statsData['totalRequests'])
        : 'N/A';
    $avgLat = $statsData['averageLatencyMs'] ?? 'N/A';
    echo "✓ Public 24h Stats:\n";
    echo "   Total Requests: {$totalReq}\n";
    echo "   Average Response Time: {$avgLat} ms\n";
} catch (Throwable $e) {
    handleExampleException('Health & Stats', $e);
}
