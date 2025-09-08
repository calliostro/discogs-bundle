# 🎵 Discogs Bundle

[![Package Version](https://img.shields.io/packagist/v/calliostro/discogs-bundle.svg)](https://packagist.org/packages/calliostro/discogs-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/discogs-bundle.svg)](https://packagist.org/packages/calliostro/discogs-bundle)
[![License](https://poser.pugx.org/calliostro/discogs-bundle/license)](https://packagist.org/packages/calliostro/discogs-bundle)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![CI](https://github.com/calliostro/discogs-bundle/actions/workflows/ci.yml/badge.svg?branch=legacy%2Fv3.x)](https://github.com/calliostro/discogs-bundle/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/calliostro/discogs-bundle/branch/legacy%2Fv3.x/graph/badge.svg?token=3ATEFYF7A0)](https://codecov.io/gh/calliostro/discogs-bundle)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-Level%208-brightgreen.svg)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-Symfony-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

> 🚀 **Seamless integration of [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api) into Symfony 6.4, 7, and 8.**  
>
> Use [v4.0.0](https://github.com/calliostro/discogs-bundle) for new projects with modern tooling and breaking changes. Legacy support for v3.x continues on the [`legacy/v3.x`](https://github.com/calliostro/discogs-bundle/tree/legacy/v3.x) branch.

## ⚡ Requirements

- **PHP**: 8.1 or higher
- **Symfony**: 6.4, 7.x, or 8.x

## 📦 Installation

### Symfony Flex (Recommended)

```console
composer require calliostro/discogs-bundle
```

### Without Symfony Flex

1️⃣ **Install the Bundle**

```console
composer require calliostro/discogs-bundle
```

2️⃣ **Register the Bundle**

Add to `config/bundles.php`:

```php
// config/bundles.php
return [
    // ...existing bundles...
    Calliostro\DiscogsBundle\CalliostroDiscogsBundle::class => ['all' => true],
];
```

## 🎸 Usage

The bundle provides a `DiscogsClient` service for autowiring:

```php
// src/Controller/MusicController.php
use Discogs\DiscogsClient;

class MusicController
{
    public function getArtist(DiscogsClient $discogs): Response
    {
        $artist = $discogs->getArtist(['id' => 8760]);

        return new JsonResponse([
            'name' => $artist['name'],
            'profile' => $artist['profile'] ?? null,
        ]);
    }
}
```

## ⚙️ Configuration

Create `config/packages/calliostro_discogs.yaml`:

```yaml
# config/packages/calliostro_discogs.yaml
calliostro_discogs:
    # Required: HTTP User-Agent header for API requests
    user_agent: 'MyApp/1.0 +https://myapp.com'

    # Recommended: Your application credentials from discogs.com/applications
    consumer_key: ~
    consumer_secret: ~

    # Rate limiting configuration
    throttle:
        enabled: true
        microseconds: 1000000  # Wait time when the rate limit is hit

    # OAuth 1.0a authentication (for user-specific data)
    oauth:
        enabled: false
        token_provider: calliostro_discogs.hwi_oauth_token_provider
```

### 🔐 Authentication

#### Basic Authentication (Recommended)

Register your app at [Discogs Applications](https://www.discogs.com/applications) to get:

- `consumer_key`
- `consumer_secret`

This enables access to protected endpoints and higher rate limits. 🚦

#### OAuth 1.0a (Optional)

For user-specific data, OAuth 1.0a is supported via [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle):

```yaml
# config/packages/calliostro_discogs.yaml
calliostro_discogs:
    oauth:
        enabled: true
        # token_provider: calliostro_discogs.hwi_oauth_token_provider  # Default, no need to specify
```

### 🛡️ Custom Token Provider

Implement `OAuthTokenProviderInterface` for custom OAuth token handling:

```php
use Calliostro\DiscogsBundle\OAuthTokenProviderInterface;

class CustomTokenProvider implements OAuthTokenProviderInterface
{
    public function getToken(): string
    {
        // Return OAuth token
    }

    public function getTokenSecret(): string
    {
        // Return OAuth token secret
    }
}
```

## 📖 Documentation

- **API Client**: See [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api)
- **Discogs API**: [Official Documentation](https://www.discogs.com/developers)
- **Example Application**: [discogs-bundle-demo](https://github.com/calliostro/discogs-bundle-demo)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure your code follows PSR-12 standards and includes tests.

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Discogs](https://www.discogs.com/) for providing the excellent music database API
- [ricbra/RicbraDiscogsBundle](https://github.com/ricbra/RicbraDiscogsBundle) for the original inspiration

> **⭐ Star this repo if you find it useful! It helps others discover this lightweight solution.**
