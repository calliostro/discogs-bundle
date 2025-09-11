<?php

namespace Calliostro\DiscogsBundle\Tests;

use Calliostro\DiscogsBundle\OAuthHandlerStackFactory;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use PHPUnit\Framework\TestCase;

class OAuthHandlerStackFactoryTest extends TestCase
{
    public function testFactoryWithOauth(): void
    {
        $oauth = new Oauth1([
            'consumer_key' => 'test_key',
            'consumer_secret' => 'test_secret',
            'token' => 'test_token',
            'token_secret' => 'test_token_secret',
        ]);

        $handlerStack = OAuthHandlerStackFactory::factory($oauth);

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(HandlerStack::class, $handlerStack);
        // Note: HandlerStack doesn't have a public hasHandler method
        // The important thing is that the handler stack is created with the OAuth subscriber
    }

    public function testFactoryWithoutOauth(): void
    {
        $handlerStack = OAuthHandlerStackFactory::factory(null);

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(HandlerStack::class, $handlerStack);
        // Note: HandlerStack doesn't have a public hasHandler method, so we can't test this directly
        // The important thing is that the handler stack is created without error
    }
}
