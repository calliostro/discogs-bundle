<?php

namespace Calliostro\DiscogsBundle\Tests;

use Calliostro\DiscogsBundle\HWIOauthTokenProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class HWIOauthTokenProviderTest extends TestCase
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
        // Create a mock that has the getRawToken method
        $mockToken = $this->createMock(TokenInterface::class);

        // Use reflection to add the method dynamically for testing
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        // Create a test double that implements getRawToken
        $oauthToken = new class implements TokenInterface {
            public function __toString(): string
            {
                return 'test';
            }

            public function getRoleNames(): array
            {
                return [];
            }

            public function getCredentials(): mixed
            {
                return null;
            }

            public function getUser(): ?\Symfony\Component\Security\Core\User\UserInterface
            {
                return null;
            }

            public function setUser(mixed $user): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'test';
            }

            public function isAuthenticated(): bool
            {
                return true;
            }

            public function setAuthenticated(bool $isAuthenticated): void
            {
            }

            public function eraseCredentials(): void
            {
            }

            /**
             * @return array<string, mixed>
             */
            public function getAttributes(): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $attributes
             */
            public function setAttributes(array $attributes): void
            {
            }

            public function hasAttribute(string $name): bool
            {
                return false;
            }

            public function getAttribute(string $name): mixed
            {
                return null;
            }

            public function setAttribute(string $name, mixed $value): void
            {
            }

            public function __serialize(): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $data
             */
            public function __unserialize(array $data): void
            {
            }

            /**
             * @return array<string, mixed>
             */
            public function getRawToken(): array
            {
                return [
                    'oauth_token' => 'test_access_token',
                    'oauth_token_secret' => 'test_access_token_secret',
                ];
            }
        };

        $tokenStorage->method('getToken')->willReturn($oauthToken);

        $provider = new HWIOauthTokenProvider($tokenStorage);

        $this->assertEquals('test_access_token', $provider->getToken());
        $this->assertEquals('test_access_token_secret', $provider->getTokenSecret());
    }

    public function testGetTokenWithIncompleteRawToken(): void
    {
        // Test token with getRawToken but missing oauth_token_secret
        $oauthToken = new class implements TokenInterface {
            public function __toString(): string
            {
                return 'test';
            }

            public function getRoleNames(): array
            {
                return [];
            }

            public function getCredentials(): mixed
            {
                return null;
            }

            public function getUser(): ?\Symfony\Component\Security\Core\User\UserInterface
            {
                return null;
            }

            public function setUser(mixed $user): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'test';
            }

            public function isAuthenticated(): bool
            {
                return true;
            }

            public function setAuthenticated(bool $isAuthenticated): void
            {
            }

            public function eraseCredentials(): void
            {
            }

            /**
             * @return array<string, mixed>
             */
            public function getAttributes(): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $attributes
             */
            public function setAttributes(array $attributes): void
            {
            }

            public function hasAttribute(string $name): bool
            {
                return false;
            }

            public function getAttribute(string $name): mixed
            {
                return null;
            }

            public function setAttribute(string $name, mixed $value): void
            {
            }

            public function __serialize(): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $data
             */
            public function __unserialize(array $data): void
            {
            }

            /**
             * @return array<string, mixed>
             */
            public function getRawToken(): array
            {
                return [
                    'oauth_token' => 'test_access_token',
                    // oauth_token_secret is missing
                ];
            }
        };

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($oauthToken);

        $provider = new HWIOauthTokenProvider($tokenStorage);

        $this->assertEquals('test_access_token', $provider->getToken());
        $this->assertEquals('', $provider->getTokenSecret()); // Missing key returns an empty string
    }
}
