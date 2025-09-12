# Upgrade Guide

## 🚀 v4.0.0 – Complete Rewrite

**v4.0.0 is a complete rewrite and fresh start.** If you're coming from v2.x or earlier, this is essentially a new bundle with the same name, but with modern architecture and much better developer experience.

### 📈 Migration from v2.x

**Coming from v2.x?** While this is technically a new bundle, migration is straightforward and brings significant benefits. Here's what you need to know:

#### ⚡ Why Upgrade?

- **📦 Simpler Installation** – No complex OAuth setup required
- **🔑 Better Authentication** – Personal Access Tokens (much easier than v2.x OAuth)
- **🚀 Better Performance** – Modern PHP 8.1+ with an optimized HTTP client
- **🛡️ Type Safety** – Full PHPStan Level 8 compliance with better IDE support
- **📖 Consistent APIs** – All method names follow clear patterns
- **🔧 Less Configuration** – Works out of the box for most use cases
- **🛠️ Clean Migration** – Clear migration path with comprehensive documentation

#### 🔄 Quick Migration Steps

1. **Update Composer** – `composer require calliostro/discogs-bundle:^4.0`
2. **Update Type Hints** – Change `DiscogsClient` → `DiscogsApiClient`
3. **Update Service References** – Change service alias if using container directly
4. **Simplify Config** – Use Personal Access Token instead of OAuth
5. **Update Method Names** – Some methods have clearer names (see below)
6. **Test & Deploy** – Your app will be faster and more reliable!

### 🎯 What's New in v4.0.0

- **Modern PHP 8.1+ Architecture** – Built with modern PHP features and type safety
- **Symfony 6.4+ Integration** – Full support for current Symfony versions  
- **Personal Access Token Support** – Simple authentication with Discogs tokens
- **Built-in OAuth 1.0a** – No external dependencies required
- **Consistent API Methods** – All 60 Discogs API endpoints with verb-first naming
- **Zero Configuration** – Works out of the box for public API access

### 📋 System Requirements

- **PHP**: 8.1+
- **Symfony**: 6.4+ | 7.x | 8.x
- **calliostro/php-discogs-api**: v4.0.0-beta.1+

### 📦 Fresh Installation

```bash
composer require calliostro/discogs-bundle:^4.0
```

### 🚀 Quick Start

#### 1. Configure the Bundle

```yaml
# config/packages/calliostro_discogs.yaml
calliostro_discogs:
    # Personal Access Token (recommended)
    personal_access_token: '%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%'
    
    # Optional: Consumer credentials for OAuth
    # consumer_key: '%env(DISCOGS_CONSUMER_KEY)%'
    # consumer_secret: '%env(DISCOGS_CONSUMER_SECRET)%'
    
    # Optional: User-Agent and throttling
    # user_agent: 'MyApp/1.0 +https://myapp.com'
    # throttle:
    #     enabled: true
    #     microseconds: 1000000
```

#### 2. Use in Your Controllers/Services

```php
<?php

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
            'releases' => $releases['releases']
        ]);
    }
}
```

### 🔑 Authentication Options

#### Personal Access Token (Recommended)

Get your token from [Discogs Developer Settings](https://www.discogs.com/settings/developers):

```yaml
calliostro_discogs:
    personal_access_token: 'your-personal-access-token'
```

#### OAuth Consumer Credentials

For applications requiring OAuth authentication:

```yaml
calliostro_discogs:
    consumer_key: 'your-consumer-key'
    consumer_secret: 'your-consumer-secret'
```

#### Anonymous Access

For public data only (rate limited):

```yaml
calliostro_discogs:
    user_agent: 'MyApp/1.0 +https://myapp.com'
```

### � Key Migration Changes (v2.x → v4.0)

#### Type Hints & Imports

```php
// v2.x
use Discogs\DiscogsClient;

public function show(DiscogsClient $discogs): Response
{
    // ...
}

// v4.0
use Calliostro\Discogs\DiscogsApiClient;

public function show(DiscogsApiClient $discogs): Response
{
    // ...
}
```

#### Service Container Changes

```php
// v2.x - Old service alias
$discogsClient = $container->get('Discogs\DiscogsClient');

// v4.0 - New service alias
$discogsClient = $container->get('Calliostro\Discogs\DiscogsApiClient');
```

#### Configuration Changes

```yaml
# v2.x - Complex OAuth setup
calliostro_discogs:
    oauth:
        enabled: true
        consumer_key: '%env(DISCOGS_CONSUMER_KEY)%'
        consumer_secret: '%env(DISCOGS_CONSUMER_SECRET)%'
        # ... more complex configuration ...

# v4.0 - Simple Personal Access Token
calliostro_discogs:
    personal_access_token: '%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%'
```

#### Most Common Method Changes

| v2.x Method                         | v4.0 Method                         | Notes                 |
|-------------------------------------|-------------------------------------|-----------------------|
| `getProfile(['username' => $user])` | `getUser(['username' => $user])`    | Clearer naming        |
| `getArtistReleases(['id' => $id])`  | `listArtistReleases(['id' => $id])` | Consistent verb-first |
| `getCollectionFolders([...])`       | `listCollectionFolders([...])`      | Consistent verb-first |
| `getUserWants([...])`               | `getUserWantlist([...])`            | Clearer naming        |
| `getInventory([...])`               | `getUserInventory([...])`           | More specific         |

**Note:** Most methods stay the same! Parameters and return values are identical.

### �📖 API Methods

All 60 Discogs API endpoints are available with consistent verb-first naming:

**Popular Methods:**

- `getArtist(['id' => $id])` – Get artist information
- `listArtistReleases(['id' => $id])` – Get artist's releases
- `getRelease(['id' => $id])` – Get release information
- `search(['q' => 'query'])` – Search the database
- `listCollectionItems(['username' => $username])` – Get user's collection
- `addToCollection(['folder_id' => 1, 'release_id' => $id])` – Add to a collection
- `getUserWantlist(['username' => $username])` – Get user's wantlist

See [README.md](README.md) for complete API documentation.

### ✅ Migration Checklist (v2.x → v4.0)

Use this checklist to ensure a smooth migration:

- [ ] **Backup your current implementation** (just in case)
- [ ] **Get a Personal Access Token** from [Discogs Developer Settings](https://www.discogs.com/settings/developers)
- [ ] **Update composer.json**: `composer require calliostro/discogs-bundle:^4.0`
- [ ] **Update imports**: Find/replace `use Discogs\DiscogsClient;` → `use Calliostro\Discogs\DiscogsApiClient;`
- [ ] **Update type hints**: Find/replace `DiscogsClient` → `DiscogsApiClient`
- [ ] **Simplify configuration**: Replace OAuth config with Personal Access Token
- [ ] **Update method calls**: Check the method mapping table above
- [ ] **Run your tests**: Ensure everything works as expected
- [ ] **Deploy & enjoy**: Your app is now more modern and maintainable!

### 🛠️ Find & Replace Commands

These commands help you find code that might need updating:

```bash
# Find old type hints and imports
grep -r "DiscogsClient" /path/to/your/project --exclude-dir=vendor

# Find methods that changed names
grep -r "getProfile\|getArtistReleases\|getCollectionFolders\|getUserWants\|getInventory" /path/to/your/project --exclude-dir=vendor

# Find old configuration patterns
grep -r "oauth:" /path/to/your/config --include="*.yaml"
```

### 🧪 Testing

```bash
# Run tests
composer test

# Run integration tests (requires API credentials)
composer test-integration

# Code analysis
composer analyse
composer cs-fix
```

### 📚 Documentation

- **Bundle Documentation**: [README.md](README.md)
- **API Documentation**: [Discogs API Docs](https://www.discogs.com/developers/)
- **Integration Tests**: [INTEGRATION_TESTS.md](INTEGRATION_TESTS.md)

### 🆘 Need Help?

- **Issues**: [GitHub Issues](https://github.com/calliostro/discogs-bundle/issues)
- **API Client**: [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api)

---

**Welcome to v4.0.0! The most modern and developer-friendly version yet!**
