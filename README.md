# ⚡ Discogs Client Bundle for Symfony – Complete Music Database Access

[![Package Version](https://img.shields.io/packagist/v/calliostro/discogs-bundle.svg)](https://packagist.org/packages/calliostro/discogs-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/discogs-bundle.svg)](https://packagist.org/packages/calliostro/discogs-bundle)
[![License](https://poser.pugx.org/calliostro/discogs-bundle/license)](https://packagist.org/packages/calliostro/discogs-bundle)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![CI](https://github.com/calliostro/discogs-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/calliostro/discogs-bundle/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/calliostro/discogs-bundle/graph/badge.svg?token=3ATEFYF7A0)](https://codecov.io/gh/calliostro/discogs-bundle)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-Symfony-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

> **🚀 SYMFONY INTEGRATION!** Seamless autowiring for the complete Discogs music database API. Zero bloat, maximum performance.

Symfony bundle that integrates the **modern** [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api) into your Symfony application. Built with modern PHP 8.1+ features, dependency injection, and powered by Guzzle.

## 📦 Installation

Install via Composer:

```bash
composer require calliostro/discogs-bundle
```

## ⚙️ Configuration

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
    
    # Optional: Rate limiting (default values shown)
    # throttle:
    #     enabled: true
    #     microseconds: 1000000
```

**Personal Access Token:** You need to [get your token](https://www.discogs.com/settings/developers) from Discogs to access your account data and get higher rate limits. For read-only operations on public data, you can use anonymous access.

**Consumer Credentials:** For building applications that need OAuth authentication, [register your app](https://www.discogs.com/applications) at Discogs to get your consumer key and secret.

**User-Agent:** By default, the client uses `DiscogsClient/4.0.0 (+https://github.com/calliostro/php-discogs-api)` as User-Agent. You can override this in the configuration if needed.

## 🚀 Quick Start

### Basic Usage

```php
<?php
// src/Controller/MusicController.php

namespace App\Controller;

use Calliostro\Discogs\DiscogsApiClient;
use Symfony\Component\HttpFoundation\JsonResponse;

class MusicController
{
    public function artistInfo(string $id, DiscogsApiClient $client): JsonResponse
    {
        $artist = $client->getArtist(['id' => $id]);
        $releases = $client->listArtistReleases(['id' => $id, 'per_page' => 5]);

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
$collection = $client->listCollectionItems(['username' => 'your-username']);
$wantlist = $client->getUserWantlist(['username' => 'your-username']);

$client->addToCollection([
    'folder_id' => 1,
    'release_id' => 30359313, // Billie Eilish - Happier Than Ever
]);

$client->addToWantlist(['release_id' => 28409710]); // Taylor Swift - Midnights
```

### Search and Discovery

```php
$results = $client->search([
    'q' => 'Billie Eilish',
    'type' => 'artist',
]);

$releases = $client->listArtistReleases(['id' => '4470662']); // Billie Eilish
$release = $client->getRelease(['id' => '30359313']); // Happier Than Ever
$master = $client->getMaster(['id' => '2835729']); // Midnights master
$label = $client->getLabel(['id' => '12677']); // Interscope Records
```

## ✨ Key Features

- **Ultra-Lightweight** – Minimal Symfony integration with zero bloat for the ultra-lightweight Discogs client
- **Complete API Coverage** – All 60 Discogs API endpoints supported
- **Direct API Calls** – `$client->getArtist()` maps to `/artists/{id}`, no abstractions
- **Type Safe + IDE Support** – Full PHP 8.1+ types, PHPStan Level 8, method autocomplete  
- **Symfony Native** – Seamless autowiring with Symfony 6.4, 7.x & 8.x
- **Future-Ready** – PHP 8.5 and Symfony 8.0 compatible (beta/dev testing)
- **Well Tested** – Comprehensive test coverage, Symfony coding standards
- **Multiple Auth Methods** – Personal Access Token, OAuth 1.0a, Consumer Credentials, Anonymous

## 🎵 All Discogs API Methods as Direct Calls

- **Database Methods** – search(), getArtist(), listArtistReleases(), getRelease(), getUserReleaseRating(), updateUserReleaseRating(), deleteUserReleaseRating(), getCommunityReleaseRating(), getReleaseStats(), getMaster(), listMasterVersions(), getLabel(), listLabelReleases()
- **User Identity Methods** – getIdentity(), getUser(), updateUser(), listUserSubmissions(), listUserContributions()
- **User Collection Methods** – listCollectionFolders(), getCollectionFolder(), createCollectionFolder(), updateCollectionFolder(), deleteCollectionFolder(), listCollectionItems(), getCollectionItemsByRelease(), addToCollection(), updateCollectionItem(), removeFromCollection(), getCustomFields(), setCustomFields(), getCollectionValue()
- **User Wantlist Methods** – getUserWantlist(), addToWantlist(), updateWantlistItem(), removeFromWantlist()
- **User Lists Methods** – getUserLists(), getUserList()
- **Marketplace Methods** – getUserInventory(), getMarketplaceListing(), createMarketplaceListing(), updateMarketplaceListing(), deleteMarketplaceListing(), getMarketplaceFee(), getMarketplaceFeeByCurrency(), getMarketplacePriceSuggestions(), getMarketplaceStats(), getMarketplaceOrder(), getMarketplaceOrders(), updateMarketplaceOrder(), getMarketplaceOrderMessages(), addMarketplaceOrderMessage()
- **Inventory Export Methods** – createInventoryExport(), listInventoryExports(), getInventoryExport(), downloadInventoryExport()
- **Inventory Upload Methods** – addInventoryUpload(), changeInventoryUpload(), deleteInventoryUpload(), listInventoryUploads(), getInventoryUpload()

*All 60 Discogs API endpoints are supported with clean documentation — see [Discogs API Documentation](https://www.discogs.com/developers/) for complete method reference*

## 📋 Requirements

- php ^8.1
- symfony ^6.4 | ^7.0 | ^8.0
- calliostro/php-discogs-api ^4.0

## 🔧 Service Integration

```php
<?php
// src/Service/MusicService.php

namespace App\Service;

use Calliostro\Discogs\DiscogsApiClient;

class MusicService
{
    public function __construct(
        private readonly DiscogsApiClient $client
    ) {
    }

    public function getArtistWithReleases(int $artistId): array
    {
        $artist = $this->client->getArtist(['id' => $artistId]);
        $releases = $this->client->listArtistReleases([
            'id' => $artistId,
            'per_page' => 10,
        ]);

        return [
            'artist' => $artist,
            'releases' => $releases['releases'],
        ];
    }

    public function addToMyCollection(int $releaseId): void
    {
        // Requires Personal Access Token
        $this->client->addToCollection([
            'folder_id' => 1, // "Uncategorized" folder
            'release_id' => $releaseId,
        ]);
    }
}
```

## 🧪 Testing

```bash
# Run unit tests (default, fast)
composer test

# Integration tests with real API (requires credentials)  
composer test-integration

# Code analysis & style
composer analyse
composer cs-fix
```

See [INTEGRATION_TESTS.md](INTEGRATION_TESTS.md) for API test setup.

## 📖 API Documentation Reference

For complete API documentation including all available parameters, visit the [Discogs API Documentation](https://www.discogs.com/developers/).

### Popular Methods

#### Database Methods

- `search($params)` – Search the Discogs database
- `getArtist($params)` – Get artist information
- `listArtistReleases($params)` – Get artist's releases
- `getRelease($params)` – Get release information
- `getUserReleaseRating($params)` – Get user's rating for a release (auth required)
- `getMaster($params)` – Get master release information
- `getLabel($params)` – Get label information

#### Collection Methods

- `listCollectionFolders($params)` – Get user's collection folders
- `listCollectionItems($params)` – Get user's collection items  
- `addToCollection($params)` – Add release to a collection (auth required)
- `updateCollectionItem($params)` – Update collection item (auth required)
- `removeFromCollection($params)` – Remove from a collection (auth required)
- `getCollectionValue($params)` – Get collection value estimation

#### User Methods

- `getUser($params)` – Get user profile information
- `getIdentity($params)` – Get current user identity (auth required)
- `listUserSubmissions($params)` – Get user's submissions
- `listUserContributions($params)` – Get user's contributions
- `getUserWantlist($params)` – Get user's wantlist
- `getUserInventory($params)` – Get user's marketplace inventory

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes:
   - Add unit tests for new functionality
   - Update integration tests if needed
   - Ensure all tests pass: `composer test`
4. Follow code standards: `composer analyse && composer cs-fix`
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

Please ensure your code follows Symfony coding standards and includes tests.

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Discogs](https://www.discogs.com/) for providing the excellent music database API
- [Symfony](https://symfony.com) for the robust framework and DI container
- [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api) for the modern client library

---

> **⭐ Star this repo** if you find it useful! It helps others discover this lightweight solution.
