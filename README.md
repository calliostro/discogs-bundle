# Discogs API Bundle for Symfony

[![Package Version](https://img.shields.io/packagist/v/calliostro/discogs-bundle.svg)](https://packagist.org/packages/calliostro/discogs-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/discogs-bundle.svg)](https://packagist.org/packages/calliostro/discogs-bundle)
[![License](https://poser.pugx.org/calliostro/discogs-bundle/license)](https://packagist.org/packages/calliostro/discogs-bundle)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![CI](https://github.com/calliostro/discogs-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/calliostro/discogs-bundle/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/calliostro/discogs-bundle/graph/badge.svg?token=3ATEFYF7A0)](https://codecov.io/gh/calliostro/discogs-bundle)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-PSR12-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

A Symfony bundle integrating [`calliostro/php-discogs-api`](https://github.com/calliostro/php-discogs-api) into your Symfony application. Provides dependency injection, autowiring, built-in retry resilience, and optional rate limiting for PHP 8.1+ and Symfony 6.4, 7.x, and 8.x.

## Installation

Install via Composer:

```bash
composer require calliostro/discogs-bundle
```

## Configuration

Configure the bundle in `config/packages/calliostro_discogs.yaml`:

```yaml
calliostro_discogs:
    # Recommended: Personal Access Token (get from https://www.discogs.com/settings/developers)
    personal_access_token: '%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%'

    # Alternative: Consumer credentials for OAuth applications
    # consumer_key: '%env(DISCOGS_CONSUMER_KEY)%'
    # consumer_secret: '%env(DISCOGS_CONSUMER_SECRET)%'

    # Optional: HTTP User-Agent header for API requests
    # user_agent: 'MyApp/1.0 +https://myapp.com'

    # Optional: Retry resilience settings (enabled by default)
    # auto_retry: true     # Automatically wait and retry on 429 and 503 responses (default: true)
    # max_retries: 3       # Maximum number of retry attempts (default: 3)

    # Optional: Proactive rate limiting (requires symfony/rate-limiter)
    # rate_limiter: discogs_api
```

> [!NOTE]
> By default, the client uses `DiscogsClient/4.1.0 (+https://github.com/calliostro/php-discogs-api)` as User-Agent. You can override this in the configuration if needed.

### Authentication Methods

- **Personal Access Token:** Obtain your token from [Discogs Developer Settings](https://www.discogs.com/settings/developers) to access user-specific data (collections, wantlists) and benefit from higher rate limits (60 requests/min).
- **Consumer Credentials:** For OAuth applications, register your application on Discogs to obtain your `consumer_key` and `consumer_secret`.
- **Anonymous Access:** If no credentials are configured, the bundle initializes the client for public data endpoints (subject to unauthenticated rate limits of 25 requests/min).

## Quick Start

### Basic Usage

Inject the `DiscogsClient` service directly into your controllers or services:

```php
<?php

namespace App\Controller;

use Calliostro\Discogs\DiscogsClient;
use Symfony\Component\HttpFoundation\JsonResponse;

final class MusicController
{
    public function artistInfo(string $id, DiscogsClient $client): JsonResponse
    {
        $artist = $client->getArtist(artistId: (int) $id);
        $releases = $client->listArtistReleases(artistId: (int) $id, perPage: 5);

        return new JsonResponse([
            'artist' => $artist['name'],
            'profile' => $artist['profile'] ?? null,
            'releases' => $releases['releases'],
        ]);
    }
}
```

### Collection and Wantlist

```php
// Requires Personal Access Token
$collection = $client->listCollectionItems(username: 'your-username', folderId: 0);
$wantlist = $client->getUserWantlist(username: 'your-username');

$client->addToCollection(
    username: 'your-username',
    folderId: 1,
    releaseId: 30359313
);

$client->addToWantlist(
    username: 'your-username',
    releaseId: 28409710
);
```

### Search and Database Lookups

```php
$results = $client->search(
    q: 'Billie Eilish',
    type: 'artist'
);

$releases = $client->listArtistReleases(artistId: 4470662);
$release = $client->getRelease(releaseId: 30359313);
$master = $client->getMaster(masterId: 2835729);
$label = $client->getLabel(labelId: 12677);
```

## Key Features

- **Lightweight Integration** – Minimal footprint with zero overhead on top of `calliostro/php-discogs-api`.
- **Complete API Coverage** – All 60 Discogs API endpoints supported.
- **Direct API Calls** – `$client->getArtist(artistId: 123)` maps directly to `/artists/{id}`.
- **Built-in Retry Resilience** – Automatic exponential backoff and retry handling for `429 Too Many Requests` and `503 Service Unavailable` responses.
- **Type Safe & IDE Support** – PHP 8.1+ types, named parameters, and PHPStan Level 8 static analysis.
- **Symfony Native** – Autowiring support for Symfony 6.4, 7.x, and 8.x.
- **Multiple Authentication Methods** – Personal Access Token, OAuth 1.0a, Consumer Credentials, and Anonymous access.

## Supported Discogs API Methods

- **Database Methods** – `search()`, `getArtist()`, `listArtistReleases()`, `getRelease()`, `getUserReleaseRating()`, `updateUserReleaseRating()`, `deleteUserReleaseRating()`, `getCommunityReleaseRating()`, `getReleaseStats()`, `getMaster()`, `listMasterVersions()`, `getLabel()`, `listLabelReleases()`
- **User Identity Methods** – `getIdentity()`, `getUser()`, `updateUser()`, `listUserSubmissions()`, `listUserContributions()`
- **User Collection Methods** – `listCollectionFolders()`, `getCollectionFolder()`, `createCollectionFolder()`, `updateCollectionFolder()`, `deleteCollectionFolder()`, `listCollectionItems()`, `getCollectionItemsByRelease()`, `addToCollection()`, `updateCollectionItem()`, `removeFromCollection()`, `getCustomFields()`, `setCustomFields()`, `getCollectionValue()`
- **User Wantlist Methods** – `getUserWantlist()`, `addToWantlist()`, `updateWantlistItem()`, `removeFromWantlist()`
- **User Lists Methods** – `getUserLists()`, `getUserList()`
- **Marketplace Methods** – `getUserInventory()`, `getMarketplaceListing()`, `createMarketplaceListing()`, `updateMarketplaceListing()`, `deleteMarketplaceListing()`, `getMarketplaceFee()`, `getMarketplaceFeeByCurrency()`, `getMarketplacePriceSuggestions()`, `getMarketplaceStats()`, `getMarketplaceOrder()`, `getMarketplaceOrders()`, `updateMarketplaceOrder()`, `getMarketplaceOrderMessages()`, `addMarketplaceOrderMessage()`
- **Inventory Export Methods** – `createInventoryExport()`, `listInventoryExports()`, `getInventoryExport()`, `downloadInventoryExport()`
- **Inventory Upload Methods** – `addInventoryUpload()`, `changeInventoryUpload()`, `deleteInventoryUpload()`, `listInventoryUploads()`, `getInventoryUpload()`

> [!NOTE]
> Complete method documentation and endpoint parameters can be found in the [Discogs API Documentation](https://www.discogs.com/developers/).

## Requirements

- **PHP** `^8.1` (tested on PHP 8.1–8.6)
- **Symfony** `^6.4 || ^7.0 || ^8.0`
- **calliostro/php-discogs-api** `^4.1`

## Resilience & Rate Limiting

### Built-in Retries (Reactive)

Out of the box, `calliostro/php-discogs-api` v4.1 automatically handles rate limit responses (`429 Too Many Requests`) and temporary service downtime (`503 Service Unavailable`). When triggered, the client sleeps for the duration requested by Discogs (via the `Retry-After` header) or uses exponential backoff before retrying the request.

You can configure or disable this behavior in `config/packages/calliostro_discogs.yaml`:

```yaml
calliostro_discogs:
    auto_retry: true   # default: true
    max_retries: 3     # default: 3
```

### Symfony Rate Limiter (Proactive, Optional)

For high-volume batch processing, background workers, or scraping tasks, use `symfony/rate-limiter` to throttle outgoing requests client-side before sending them:

```bash
composer require symfony/rate-limiter
```

#### 1. Configure the Rate Limiter

```yaml
# config/packages/rate_limiter.yaml
rate_limiter:
    discogs_api:
        policy: 'sliding_window'
        limit: 25  # 25 for anonymous access, up to 60 for authenticated access
        interval: '1 minute'
```

#### 2. Assign to the Bundle

```yaml
# config/packages/calliostro_discogs.yaml
calliostro_discogs:
    personal_access_token: '%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%'
    rate_limiter: discogs_api
```

## Contributing

Contributions are welcome! Please ensure that all tests pass and code quality checks succeed:

```bash
composer cs-fix
composer analyse
composer test
```

## License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## Disclaimer

Discogs is a registered trademark of Zink Media, LLC. This project is an independent, unofficial open-source library and is not affiliated with, endorsed by, or sponsored by Discogs or Zink Media, LLC.

## Acknowledgments

- [Discogs](https://www.discogs.com/) for providing the database and API.
- [Symfony](https://symfony.com) for the web framework and dependency injection container.
- Underlying client: [`calliostro/php-discogs-api`](https://github.com/calliostro/php-discogs-api).
- Sister Symfony bundles: [`calliostro/spotify-web-api-bundle`](https://github.com/calliostro/spotify-web-api-bundle), [`calliostro/last-fm-client-bundle`](https://github.com/calliostro/last-fm-client-bundle), and [`calliostro/musicbrainz-bundle`](https://github.com/calliostro/musicbrainz-bundle).
