<?php

namespace Calliostro\DiscogsBundle\Tests;

use Calliostro\DiscogsBundle\HWIOauthTokenProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class HWIOauthTokenProviderTest extends TestCase
{
    public function testGetTokenWithNullToken(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $provider = new HWIOauthTokenProvider($tokenStorage);

        $this->assertEquals('', $provider->getToken());
        $this->assertEquals('', $provider->getTokenSecret());
    }

    public function testGetTokenWithTokenWithoutRawTokenMethod(): void
    {
        $mockToken = $this->createMock(TokenInterface::class);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($mockToken);

        $provider = new HWIOauthTokenProvider($tokenStorage);

        $this->assertEquals('', $provider->getToken());
        $this->assertEquals('', $provider->getTokenSecret());
    }

    public function testGetTokenWithValidOAuthToken(): void
    {
        $oauthToken = new MockOAuthToken([
            'oauth_token' => 'test_access_token',
            'oauth_token_secret' => 'test_access_token_secret',
        ]);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($oauthToken);

        $provider = new HWIOauthTokenProvider($tokenStorage);

        $this->assertEquals('test_access_token', $provider->getToken());
        $this->assertEquals('test_access_token_secret', $provider->getTokenSecret());
    }

    public function testGetTokenWithIncompleteRawToken(): void
    {
        // Test token with getRawToken but missing oauth_token_secret
        $oauthToken = new MockOAuthToken([
            'oauth_token' => 'test_access_token',
            // oauth_token_secret is intentionally missing
        ]);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($oauthToken);

        $provider = new HWIOauthTokenProvider($tokenStorage);

        $this->assertEquals('test_access_token', $provider->getToken());
        $this->assertEquals('', $provider->getTokenSecret()); // Missing key returns an empty string
    }
}
