<?php

namespace Calliostro\DiscogsBundle\Tests;

use Calliostro\DiscogsBundle\OAuthSubscriberFactory;
use Calliostro\DiscogsBundle\OAuthTokenProviderInterface;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use PHPUnit\Framework\TestCase;

class OAuthSubscriberFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $tokenProvider = new class implements OAuthTokenProviderInterface {
            public function getToken(): string
            {
                return 'test_token';
            }

            public function getTokenSecret(): string
            {
                return 'test_token_secret';
            }
        };

        $oauth = OAuthSubscriberFactory::factory(
            $tokenProvider,
            'test_consumer_key',
            'test_consumer_secret'
        );

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(Oauth1::class, $oauth);
    }
}
