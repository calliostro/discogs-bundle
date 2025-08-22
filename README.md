Discogs Bundle
==============

[![Build Status](https://api.travis-ci.com/calliostro/discogs-bundle.svg)](https://app.travis-ci.com/github/calliostro/discogs-bundle)
[![Version](https://poser.pugx.org/calliostro/discogs-bundle/version)](//packagist.org/packages/calliostro/discogs-bundle)
[![License](https://poser.pugx.org/calliostro/discogs-bundle/license)](//packagist.org/packages/calliostro/discogs-bundle)

This bundle provides a simple integration of the
[calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api) into Symfony 6.4+ and Symfony 7.

For more information about the Discogs API, visit https://www.discogs.com/developers.

## Requirements

- **PHP**: 8.1 or higher
- **Symfony**: 6.4 or 7.x

## Installation

### Applications using Symfony Flex

```console
composer require calliostro/discogs-bundle
```

### Applications not using Symfony Flex

#### Step 1: Install the Bundle

```console
composer require calliostro/discogs-bundle
```

#### Step 2: Register the Bundle

Add the bundle to `config/bundles.php`:

```php
// config/bundles.php
return [
    // ...
    Calliostro\DiscogsBundle\CalliostroDiscogsBundle::class => ['all' => true],
];
```

## Usage

The bundle provides a `DiscogsClient` service that can be autowired:

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

## Configuration

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

### Authentication

#### Basic Authentication (Recommended)
Register your application at https://www.discogs.com/applications to get:
- `consumer_key`
- `consumer_secret`

This enables access to protected endpoints and higher rate limits.

#### OAuth 1.0a (Optional)
For user-specific data access, OAuth 1.0a is required. This bundle includes support for [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle):

```yaml
# config/packages/calliostro_discogs.yaml
calliostro_discogs:
    oauth:
        enabled: true
        # token_provider: calliostro_discogs.hwi_oauth_token_provider  # Default, no need to specify
```

### Custom Token Provider

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

## Documentation

- **API Client**: See [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api)
- **Discogs API**: [Official Documentation](https://www.discogs.com/developers)
- **Example Application**: [discogs-bundle-demo](https://github.com/calliostro/discogs-bundle-demo)

## Contributing

Found a bug or missing feature? Please [create an issue](https://github.com/calliostro/discogs-bundle/issues) or submit a pull request.

## Credits

This bundle is based on [ricbra/RicbraDiscogsBundle](https://github.com/ricbra/RicbraDiscogsBundle) for Symfony 2.
