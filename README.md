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
    
    # Optional: Professional rate limiting (requires symfony/rate-limiter)
    # rate_limiter: discogs_api       # Your configured RateLimiterFactory service
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

use Calliostro\Discogs\DiscogsClient;
use Symfony\Component\HttpFoundation\JsonResponse;

class MusicController
{
    public function artistInfo(string $id, DiscogsClient $client): JsonResponse
    {
        $artist = $client->getArtist(artistId: $id);
        $releases = $client->listArtistReleases(artistId: $id, perPage: 5);

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
    releaseId: 30359313 // Billie Eilish - Happier Than Ever
);

$client->addToWantlist(username: 'your-username', releaseId: 28409710); // Taylor Swift - Midnights
```

### Search and Discovery

```php
$results = $client->search(
    q: 'Billie Eilish',
    type: 'artist'
);

$releases = $client->listArtistReleases(artistId: 4470662);  // Billie Eilish
$release = $client->getRelease(releaseId: 30359313);         // Happier Than Ever
$master = $client->getMaster(masterId: 2835729);             // Midnights master
$label = $client->getLabel(labelId: 12677);                  // Interscope Records
```

## ✨ Key Features

- **Ultra-Lightweight** – Minimal Symfony integration with zero bloat for the ultra-lightweight Discogs client
- **Complete API Coverage** – All 60 Discogs API endpoints supported
- **Direct API Calls** – `$client->getArtist(id: 123)` maps to `/artists/{id}`, no abstractions
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

use Calliostro\Discogs\DiscogsClient;

class MusicService
{
    public function __construct(
        private readonly DiscogsClient $client
    ) {
    }

    public function getArtistWithReleases(int $artistId): array
    {
        $artist = $this->client->getArtist(artistId: $artistId);
        $releases = $this->client->listArtistReleases(
            artistId: $artistId,
            perPage: 10
        );

        return [
            'artist' => $artist,
            'releases' => $releases['releases'],
        ];
    }

    public function addToMyCollection(int $releaseId): void
    {
        // Requires Personal Access Token
        $this->client->addToCollection(
            username: 'your-username', // Replace with actual username
            folderId: 1, // "Uncategorized" folder
            releaseId: $releaseId
        );
    }
}
```

## ⚡ Rate Limiting (Optional)

For high-volume applications, use the powerful [symfony/rate-limiter](https://symfony.com/doc/current/rate_limiter.html) component:

```bash
composer require symfony/rate-limiter
```

### 1. Configure Rate Limiter

```yaml
# config/packages/rate_limiter.yaml
rate_limiter:
    discogs_api:
        policy: 'sliding_window'
        limit: 25  # Safe for both anonymous and authenticated access
        interval: '1 minute'
```

### 2. Configure Bundle

```yaml
# config/packages/calliostro_discogs.yaml
calliostro_discogs:
    personal_access_token: '%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%'
    rate_limiter: discogs_api
```

**Choose your rate limit based on your authentication:**

- **Anonymous access:** Use 25/min (as shown above)
- **Authenticated only:** Change limit to 60 for maximum performance

## 🤝 Contributing

Contributions are welcome! Please see [DEVELOPMENT.md](DEVELOPMENT.md) for detailed setup instructions, testing guide, and development workflow.

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Discogs](https://www.discogs.com/) for providing the excellent music database API
- [Symfony](https://symfony.com) for the robust framework and DI container
- [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api) for the modern client library

---

> **⭐ Star this repo** if you find it useful! It helps others discover this lightweight solution.
