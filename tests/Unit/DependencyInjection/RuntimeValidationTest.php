<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit\DependencyInjection;

use Calliostro\DiscogsBundle\DependencyInjection\DiscogsClientFactory;
use PHPUnit\Framework\TestCase;

final class RuntimeValidationTest extends TestCase
{
    private DiscogsClientFactory $factory;

    public function testValidPersonalAccessTokenCreatesClient(): void
    {
        $client = $this->factory->createClient('valid_token_123456', null, null, []);
        $this->assertInstanceOf('Calliostro\\Discogs\\DiscogsClient', $client);
    }

    public function testShortPersonalAccessTokenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Personal access token must be at least 10 characters long');

        $this->factory->createClient('short', null, null, []);
    }

    public function testValidConsumerCredentialsCreateClient(): void
    {
        $client = $this->factory->createClient(null, 'consumer_key', 'consumer_secret', []);
        $this->assertInstanceOf('Calliostro\\Discogs\\DiscogsClient', $client);
    }

    public function testPartialConsumerCredentialsThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incomplete OAuth credentials provided');

        $this->factory->createClient(null, 'consumer_key', null, []);
    }

    public function testPartialConsumerCredentialsWithSecretOnlyThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incomplete OAuth credentials provided');

        $this->factory->createClient(null, null, 'consumer_secret', []);
    }

    public function testAnonymousClientCreation(): void
    {
        $client = $this->factory->createClient(null, null, null, []);
        $this->assertInstanceOf('Calliostro\\Discogs\\DiscogsClient', $client);
    }

    public function testEmptyStringsTreatedAsNull(): void
    {
        $client = $this->factory->createClient('', '', '', []);
        $this->assertInstanceOf('Calliostro\\Discogs\\DiscogsClient', $client);
    }

    public function testWhitespaceOnlyStringsTreatedAsEmpty(): void
    {
        $client = $this->factory->createClient('   ', '   ', '   ', []);
        $this->assertInstanceOf('Calliostro\\Discogs\\DiscogsClient', $client);
    }

    public function testPersonalAccessTokenTakesPrecedenceOverConsumerCredentials(): void
    {
        // When both are provided, personal access token should be used
        $client = $this->factory->createClient('valid_token_123456', 'consumer_key', 'consumer_secret', []);
        $this->assertInstanceOf('Calliostro\\Discogs\\DiscogsClient', $client);
    }

    public function testShortPersonalAccessTokenWithValidConsumerCredentialsStillFails(): void
    {
        // Personal access token validation should happen first and fail
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Personal access token must be at least 10 characters long');

        $this->factory->createClient('short', 'consumer_key', 'consumer_secret', []);
    }

    public function testExceptionContainsHelpfulInstructions(): void
    {
        try {
            $this->factory->createClient('short', null, null, []);
            $this->fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString('Personal access token must be at least 10 characters long', $message);
            $this->assertStringContainsString('To configure Discogs API credentials:', $message);
            $this->assertStringContainsString('https://www.discogs.com/settings/developers', $message);
            $this->assertStringContainsString('%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%', $message);
        }
    }

    protected function setUp(): void
    {
        $this->factory = new DiscogsClientFactory();
    }
}
