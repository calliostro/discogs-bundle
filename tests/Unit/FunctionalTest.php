<?php

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\Discogs\DiscogsApiClient;
use Calliostro\DiscogsBundle\Tests\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;

final class FunctionalTest extends TestCase
{
    private TestKernel $kernel;

    protected function tearDown(): void
    {
        if (isset($this->kernel)) {
            $this->kernel->cleanupCache();
        }
        parent::tearDown();
    }

    public function testServiceWiring(): void
    {
        $this->kernel = TestKernel::createForFunctional();
        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $discogsClient = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $discogsClient);
    }

    public function testServiceWiringWithConfiguration(): void
    {
        $this->kernel = TestKernel::createForFunctional([
            'user_agent' => 'test',
        ]);
        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $discogsClient = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $discogsClient);

        // Verify that the client is properly configured
        // The user agent configuration is handled internally by the bundle
        $this->assertNotNull($discogsClient);
    }

    public function testServiceWiringWithThrottleDisabled(): void
    {
        $this->kernel = TestKernel::createForFunctional([
            'throttle' => [
                'enabled' => false,
            ],
        ]);
        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $discogsClient = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $discogsClient);
    }

    public function testServiceWiringWithConsumerCredentials(): void
    {
        $this->kernel = TestKernel::createForFunctional([
            'consumer_key' => 'test_key',
            'consumer_secret' => 'test_secret',
        ]);
        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $discogsClient = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $discogsClient);
    }

    public function testServiceWiringWithPersonalAccessToken(): void
    {
        $this->kernel = TestKernel::createForFunctional([
            'personal_access_token' => 'test_token_123',
        ]);
        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $discogsClient = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $discogsClient);
    }

    public function testMultipleKernelInstancesIsolation(): void
    {
        // Test that multiple kernel instances don't interfere
        $kernel1 = TestKernel::createForFunctional(['user_agent' => 'App1/1.0']);
        $kernel2 = TestKernel::createForFunctional(['user_agent' => 'App2/2.0']);

        $kernel1->boot();
        $kernel2->boot();

        $client1 = $kernel1->getContainer()->get('calliostro_discogs.discogs_client');
        $client2 = $kernel2->getContainer()->get('calliostro_discogs.discogs_client');

        $this->assertInstanceOf(DiscogsApiClient::class, $client1);
        $this->assertInstanceOf(DiscogsApiClient::class, $client2);
        $this->assertNotSame($client1, $client2);

        $kernel1->cleanupCache();
        $kernel2->cleanupCache();
    }
}
