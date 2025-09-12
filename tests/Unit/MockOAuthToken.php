<?php

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Mock OAuth token for testing purposes.
 */
final class MockOAuthToken implements TokenInterface
{
    /**
     * @param array<string, string> $rawTokenData
     */
    public function __construct(private readonly array $rawTokenData = [])
    {
    }

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

    public function getUser(): ?UserInterface
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
        return $this->rawTokenData;
    }
}
