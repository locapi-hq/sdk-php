# locapi/sdk (PHP)

## 0.3.0

### Breaking Changes

- Requests now use the public API prefix. Paths are `/api/v1/...` and `/api/health` on the origin (`https://locapi.dev` by default). Pass only the origin as the base URL; do not include `/api`.

## 0.2.0

### Minor Changes

- feat(sdk): add Address, Health, and Stats resources
  - Added `AddressResource` (`$client->addresses()->search()`, `autocomplete()`, `reverse()`, `ipWhois()`, `ipGeolocation()`)
  - Added `HealthResource` (`$client->health()->live()`, `ready()`, `status()`)
  - Added `StatsResource` (`$client->stats()->overview()`)
  - Added support for alternative names in `LocationResource`
  - Expanded unit and integration test suites

## 0.1.0

### Minor Changes

- Initial release of the official LocAPI PHP SDK
  - Fully decoupled architecture conforming to PSR-18 (HTTP Client) and PSR-17 (HTTP Factory)
  - Auto-discovery of HTTP clients via `php-http/discovery`
  - Resources for Locations, Postal Codes, Countries, Boundaries, Timezones, and Distance Matrices
