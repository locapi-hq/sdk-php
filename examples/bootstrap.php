<?php

declare(strict_types=1);

/**
 * Shared Bootstrap Utility for LocAPI PHP SDK Examples
 *
 * Handles:
 * 1. Composer autoloader registration
 * 2. Standalone .env parser (zero-dependency)
 * 3. SDK client initialization
 * 4. Structured console logging and exception handling helpers
 */

// 1. Locate and load Composer autoloader
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
];

$loaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    fwrite(STDERR, "Error: Composer dependencies not found. Run 'composer install' first.\n");
    exit(1);
}

use LocApi\LocApi;
use LocApi\Exceptions\LocApiException;
use LocApi\Exceptions\LocApiAuthenticationException;
use LocApi\Exceptions\LocApiRateLimitException;

// 2. Load .env file if present in examples/
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!isset($_SERVER[$key]) && !isset($_ENV[$key])) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

/**
 * Initialize and return the LocApi client from environment configuration.
 */
function createLocApiClient(): LocApi
{
    $apiKey = getenv('LOCAPI_API_KEY') ?: ($_ENV['LOCAPI_API_KEY'] ?? 'demo_api_key');
    $baseUrl = getenv('LOCAPI_BASE_URL') ?: ($_ENV['LOCAPI_BASE_URL'] ?? 'https://locapi.dev');

    $maskedKey = strlen($apiKey) > 8
        ? substr($apiKey, 0, 4) . '...' . substr($apiKey, -4)
        : '****';

    echo "--- LocAPI PHP SDK Example ---\n";
    echo "Connecting to: {$baseUrl}\n";
    echo "API Key: {$maskedKey}\n\n";

    return new LocApi($apiKey, $baseUrl);
}

/**
 * Helper to display structured exceptions from LocAPI.
 */
function handleExampleException(string $context, Throwable $e): void
{
    if ($e instanceof LocApiAuthenticationException) {
        fwrite(STDERR, "[{$context}] LocApiAuthenticationException (401): {$e->getMessage()}\n");
        if ($e->getErrorType()) {
            fwrite(STDERR, "   Error Type: {$e->getErrorType()}\n");
        }
    } elseif ($e instanceof LocApiRateLimitException) {
        fwrite(STDERR, "[{$context}] LocApiRateLimitException (429): {$e->getMessage()}\n");
        if ($e->getErrorType()) {
            fwrite(STDERR, "   Error Type: {$e->getErrorType()}\n");
        }
    } elseif ($e instanceof LocApiException) {
        fwrite(STDERR, "[{$context}] LocApiException ({$e->getStatusCode()}): {$e->getMessage()}\n");
        if ($e->getErrorType()) {
            fwrite(STDERR, "   Error Type: {$e->getErrorType()}\n");
        }
        if (!empty($e->getErrors())) {
            fwrite(STDERR, "   Validation Issues:\n");
            foreach ($e->getErrors() as $field => $messages) {
                $msgList = is_array($messages) ? implode(', ', $messages) : (string) $messages;
                fwrite(STDERR, "     - {$field}: {$msgList}\n");
            }
        }
    } else {
        fwrite(STDERR, "[{$context}] Error: {$e->getMessage()}\n");
    }
}
