# 🚀 Upgrade Guide – v4.0.0 Complete Rewrite

**v4.0.0 is a complete rewrite and fresh start.** If you're coming from v2.x or earlier, this is essentially a new bundle with the same name, but with modern architecture and much better developer experience.

## 📈 Migration from v2.x

**Coming from v2.x?** While this is technically a new bundle, migration is straightforward and brings significant benefits. Here's what you need to know:

### Why Upgrade?

- **Simpler Installation**: No complex OAuth setup required
- **Better Authentication**: Personal Access Tokens (much easier than v2.x OAuth)
- **Better Performance**: Modern PHP 8.1+ with an optimized HTTP client
- **Type Safety**: Full PHPStan Level 8 compliance with better IDE support
- **Consistent APIs**: All method names follow clear patterns
- **Less Configuration**: Works out of the box for most use cases
- **Clean Migration**: Clear migration path with comprehensive documentation

### Quick Migration Steps

1. **Update Composer**: `composer require calliostro/discogs-bundle:^4.0`
2. **Update Type Hints**: Change `DiscogsApiClient` → `DiscogsClient`
3. **Update Imports**: Change namespace in use statements
4. **Simplify Config**: Use Personal Access Token instead of OAuth
5. **Update Method Names**: Some methods have clearer names (see below)
6. **Test & Deploy**: Your app will be faster and more reliable!

## 🎯 What's New in v4.0.0

- **Modern PHP 8.1+ Architecture**: Built with modern PHP features and type safety
- **Symfony 6.4+ Integration**: Full support for current Symfony versions  
- **Personal Access Token Support**: Simple authentication with Discogs tokens
- **Built-in OAuth 1.0a**: No external dependencies required
- **Consistent API Methods**: All 60 Discogs API endpoints with verb-first naming
- **Zero Configuration**: Works out of the box for public API access

## 📋 System Requirements

- **PHP**: 8.1+
- **Symfony**: 6.4+ | 7.x | 8.x
- **calliostro/php-discogs-api**: v4.0.0-beta.1+

## 📦 Fresh Installation

```bash
composer require calliostro/discogs-bundle:^4.0
```

## 🚀 Quick Migration Overview

The key difference: **v4.0 uses named parameters instead of arrays**

```php
// v4.0 - Modern approach with named parameters
$artist = $client->getArtist(artistId: $id);
$releases = $client->listArtistReleases(artistId: $id, perPage: 5);

// v2.x - Old array-based parameters (no longer supported)
// $artist = $client->getArtist(['id' => $id]);
// $releases = $client->getArtistReleases(['id' => $id, 'per_page' => 5]);
```

**Configuration is now simpler:** Use Personal Access Token instead of complex OAuth setup.

See [README.md](README.md) for complete setup and usage documentation.

## 🔄 Key Migration Changes (v2.x → v4.0)

### Type Hints & Imports

```php
// v2.x
use Discogs\DiscogsClient;

public function show(DiscogsClient $discogs): Response
{
    // ...
}

// v4.0
use Calliostro\Discogs\DiscogsClient;

public function show(DiscogsClient $discogs): Response
{
    // ...
}
```

### Configuration Changes

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

### Most Common Method Changes

| v2.x Method                         | v4.0 Method                              | Notes                                   |
|-------------------------------------|------------------------------------------|-----------------------------------------|
| `getProfile(['username' => $user])` | `getUser(username: $user)`               | Clearer naming & parameter style        |
| `getArtistReleases(['id' => $id])`  | `listArtistReleases(artistId: $id)`      | Consistent verb-first & parameter style |
| `getCollectionFolders([...])`       | `listCollectionFolders(username: $user)` | Consistent verb-first & parameter style |
| `getUserWants([...])`               | `getUserWantlist(username: $user)`       | Clearer naming & parameter style        |
| `getInventory([...])`               | `getUserInventory(username: $user)`      | More specific & parameter style         |

**Note:** Most methods stay the same! Parameters and return values are identical.

## 📖 API Changes

**All 60 Discogs API endpoints** are now available with **consistent verb-first naming** and **named parameters**.

See [README.md](README.md) for complete API documentation.

## ✅ Migration Checklist (v2.x → v4.0)

Use this checklist to ensure a smooth migration:

- **Backup your current implementation**: just in case
- **Get a Personal Access Token**: from [Discogs Developer Settings](https://www.discogs.com/settings/developers)
- **Update composer.json**: `composer require calliostro/discogs-bundle:^4.0`
- **Update imports**: Find/replace `use Discogs\DiscogsClient;` → `use Calliostro\Discogs\DiscogsClient;`
- **Update type hints**: Find/replace `DiscogsApiClient` → `DiscogsClient`
- **Simplify configuration**: Replace OAuth config with Personal Access Token
- **Update method calls**: Check the method mapping table above
- **Run your tests**: Ensure everything works as expected
- **Deploy & enjoy**: Your app is now more modern and maintainable!

## 🛠️ Find & Replace Commands

These commands help you find code that might need updating:

```bash
# Find old type hints and imports
grep -r "DiscogsApiClient" /path/to/your/project --exclude-dir=vendor

# Find methods that changed names
grep -r "getProfile\|getArtistReleases\|getCollectionFolders\|getUserWants\|getInventory" /path/to/your/project --exclude-dir=vendor

# Find old configuration patterns
grep -r "oauth:" /path/to/your/config --include="*.yaml"
```

## 📚 Documentation

- **Bundle Documentation**: [README.md](README.md)
- **API Documentation**: [Discogs API Docs](https://www.discogs.com/developers/)

## 🆘 Need Help?

- **Issues**: [GitHub Issues](https://github.com/calliostro/discogs-bundle/issues)
- **API Client**: [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api)

---

**Welcome to v4.0.0! The most modern and developer-friendly version yet!**
