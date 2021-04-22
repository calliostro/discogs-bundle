Discogs Bundle
==============

[![Build Status](https://api.travis-ci.com/calliostro/discogs-bundle.svg)](https://www.travis-ci.com/github/calliostro/discogs-bundle)
[![Version](https://poser.pugx.org/calliostro/discogs-bundle/version)](//packagist.org/packages/calliostro/discogs-bundle)
[![License](https://poser.pugx.org/calliostro/discogs-bundle/license)](//packagist.org/packages/calliostro/discogs-bundle)

This bundle provides a simple integration of the
[calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api) into Symfony 5. You can find more 
information about this library on its dedicated page at https://www.discogs.com/developers.


Installation
------------

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require calliostro/discogs-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require calliostro/discogs-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Calliostro\DiscogsBundle\CalliostroDiscogsBundle::class => ['all' => true],
];
```


Usage
-----

This bundle provides a single service for communication with Discogs API, which you can autowire by using the `Discogs` 
type-hint:

```php
// src/Controller/SomeController.php

use Discogs\DiscogsClient;
// ...

class SomeController
{
    public function index(DiscogsClient $discogs)
    {
        $artist = $discogs->getArtist([
            'id' => 8760,
        ]);

        echo $artist['name'];

        // ...
    }
}
```


Configuration
-------------

For configuration create a new `config/packages/calliostro_discogs.yaml` file. The default values are:

```yaml
# config/packages/calliostro_discogs.yaml
calliostro_discogs:

  # Freely selectable and valid HTTP user agent identification (required)
  user_agent: 'CalliostroDiscogsBundle/2.0 +https://github.com/calliostro/discogs-bundle'

  # Your consumer key (recommended)
  consumer_key: ~

  # Your consumer secret (recommended)
  consumer_secret: ~

  throttle:
    # If activated, a new attempt is made later when the rate limit is reached
    enabled: true
    # Number of milliseconds to wait until the next attempt when the rate limit is reached
    microseconds: 1000000

  oauth:
    # If enabled, full OAuth 1.0a with access token/secret is used
    enabled: false
    # You can create a service implementing OAuthTokenProviderInterface (HWIOAuthBundle is supported by default)
    token_provider: calliostro_discogs.hwi_oauth_token_provider
```

### Client Credentials

To access protected endpoints and get a higher rate limit, you must enable OAuth. For this, you must register for at 
least `consumer_key` and `consumer_secret` at https://www.discogs.com/de/applications/edit.

### User Authorization

To access current user information, you also need a user token. Discogs supports only OAuth 1.0a for user authorization.
You should use a  third-party library for this. This bundle provides support for
[hwi/HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle). The `token_provider` does not need to be changed in 
configuration file if you use the HWIOAuthBundle. 


Documentation
-------------

See [calliostro/php-discogs-api](https://github.com/calliostro/php-discogs-api) for documentation of the Discogs Client.

Further documentation can be found at the [Discogs API v2.0 Documentation](https://www.discogs.com/developers).

You find an example in [calliostro/discogs-bundle-demo](https://github.com/calliostro/discogs-bundle-demo).


Contributing
------------

Implemented a missing feature? You can request it. And creating a pull request is an even better way to get things done.


See also
--------

For the integration of Discogs into Symfony 2, see 
[ricbra/RicbraDiscogsBundle](https://github.com/ricbra/RicbraDiscogsBundle), which this is based on.
