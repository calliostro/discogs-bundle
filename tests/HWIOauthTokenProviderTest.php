<?php

namespace Calliostro\DiscogsBundle\Tests;

use Calliostro\DiscogsBundle\HWIOauthTokenProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class HWIOauthTokenProviderTest extends TestCase
{
    public function testGetTokenWithNullToken(): void
    {
        $tokenStorage = new class implements TokenStorageInterface {
            public function getToken(): ?\Symfony\Component\Security\Core\Authentication\Token\TokenInterface
            {
                return null;
            }

            public function setToken(?\Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token = null): void
            {
            }
        };

        $provider = new HWIOauthTokenProvider($tokenStorage);

        $this->assertEquals('', $provider->getToken());
        $this->assertEquals('', $provider->getTokenSecret());
    }

    public function testGetTokenWithTokenWithoutRawTokenMethod(): void
    {
        $mockToken = $this->createMock(\Symfony\Component\Security\Core\Authentication\Token\TokenInterface::class);

        $tokenStorage = new class($mockToken) implements TokenStorageInterface {
            public function __construct(private readonly ?\Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token)
            {
            }

            public function getToken(): ?\Symfony\Component\Security\Core\Authentication\Token\TokenInterface
            {
                return $this->token;
            }

            public function setToken(?\Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token = null): void
            {
            }
        };

        $provider = new HWIOauthTokenProvider($tokenStorage);

        $this->assertEquals('', $provider->getToken());
        $this->assertEquals('', $provider->getTokenSecret());
    }
}
