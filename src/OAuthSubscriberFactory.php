<?php

namespace Calliostro\DiscogsBundle;

use GuzzleHttp\Subscriber\Oauth\Oauth1;

final class OAuthSubscriberFactory
{
    public static function factory(
        OAuthTokenProviderInterface $provider,
        string $consumerKey,
        string $consumerSecret
    ): Oauth1 {
        return new Oauth1([
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'token' => $provider->getToken(),
            'token_secret' => $provider->getTokenSecret()
        ]);
    }
}
