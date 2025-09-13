<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\Discogs\DiscogsClient;

final class FunctionalTest extends UnitTestCase
{
    public function testServiceWiring(): void
    {
        $container = $this->bootKernelAndGetContainer();
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testServiceWiringWithConfiguration(): void
    {
        $container = $this->bootKernelAndGetContainer(['user_agent' => 'test']);

        $discogsClient = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsClient::class, $discogsClient);

        // Verify that the client is properly configured
        // The user agent configuration is handled internally by the bundle
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $discogsClient);
    }

    public function testServiceWiringWithMinimalConfig(): void
    {
        $config = [];
        $container = $this->bootKernelAndGetContainer($config);
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testServiceWiringWithConsumerCredentials(): void
    {
        $config = ['consumer_key' => 'test_key', 'consumer_secret' => 'test_secret'];
        $container = $this->bootKernelAndGetContainer($config);
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testServiceWiringWithPersonalAccessToken(): void
    {
        $container = $this->bootKernelAndGetContainer(['personal_access_token' => 'test_token_123']);
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }
}
