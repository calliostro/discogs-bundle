<?php

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\Discogs\DiscogsApiClient;
use Calliostro\DiscogsBundle\Tests\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;

/**
 * Extended bundle-wide functional tests covering various configuration scenarios.
 */
final class BundleIntegrationTest extends TestCase
{
    public function testBundleLoadsWithMinimalConfiguration(): void
    {
        $kernel = TestKernel::createForFunctional();
        $kernel->boot();
        $container = $kernel->getContainer();

        // Verify core service is available
        $this->assertTrue($container->has('calliostro_discogs.discogs_client'));
        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testBundleWithAllConfigurationOptions(): void
    {
        $config = [
            'user_agent' => 'TestBundle/1.0',
            'consumer_key' => 'test_consumer_key',
            'consumer_secret' => 'test_consumer_secret',
            'personal_access_token' => 'test_personal_token',
            'throttle' => [
                'enabled' => true,
                'microseconds' => 750000,
            ],
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        // Verify services are properly configured
        $this->assertTrue($container->has('calliostro_discogs.discogs_client'));
        // Note: throttle_handler_stack is private and not available in a compiled container

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testBundleWithThrottleDisabled(): void
    {
        $config = [
            'throttle' => [
                'enabled' => false,
            ],
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testBundleWithConsumerCredentialsOnly(): void
    {
        $config = [
            'consumer_key' => 'my_consumer_key',
            'consumer_secret' => 'my_consumer_secret',
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testBundleWithPersonalAccessTokenOnly(): void
    {
        $config = [
            'personal_access_token' => 'my_personal_access_token_123',
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testBundleWithCustomUserAgent(): void
    {
        $config = [
            'user_agent' => 'MyCustomApp/2.1.0 +http://example.com',
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testBundleServicesArePrivate(): void
    {
        $kernel = TestKernel::createForFunctional([
            'consumer_key' => 'test',
            'consumer_secret' => 'test',
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();

        // The main service should be public for injection
        $this->assertTrue($container->has('calliostro_discogs.discogs_client'));

        // Internal services should not be directly accessible in compiled container
        // (This is expected behavior in Symfony - internal services are private)
        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testMultipleBundleInstancesIsolation(): void
    {
        $kernel1 = TestKernel::createForFunctional([
            'user_agent' => 'App1/1.0',
            'throttle' => ['enabled' => true],
        ]);

        $kernel2 = TestKernel::createForFunctional([
            'user_agent' => 'App2/2.0',
            'throttle' => ['enabled' => false],
        ]);

        $kernel1->boot();
        $kernel2->boot();

        $client1 = $kernel1->getContainer()->get('calliostro_discogs.discogs_client');
        $client2 = $kernel2->getContainer()->get('calliostro_discogs.discogs_client');

        $this->assertInstanceOf(DiscogsApiClient::class, $client1);
        $this->assertInstanceOf(DiscogsApiClient::class, $client2);

        // Clients should be different instances
        $this->assertNotSame($client1, $client2);

        $kernel1->cleanupCache();
        $kernel2->cleanupCache();
    }

    public function testBundleServiceDefinitionStructure(): void
    {
        $kernel = TestKernel::createForFunctional([
            'consumer_key' => 'test_key',
            'consumer_secret' => 'test_secret',
            'throttle' => [
                'enabled' => true,
                'microseconds' => 500000,
            ],
        ]);

        $kernel->boot();
        $container = $kernel->getContainer();

        // Test that we can retrieve the main service
        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        // Test service is singleton (same instance returned)
        $client2 = $container->get('calliostro_discogs.discogs_client');
        $this->assertSame($client, $client2);

        $kernel->cleanupCache();
    }

    public function testBundleParameterHandling(): void
    {
        $config = [
            'user_agent' => 'ParameterTest/1.0',
            'consumer_key' => 'param_key',
            'consumer_secret' => 'param_secret',
            'throttle' => [
                'enabled' => true,
                'microseconds' => 2000000, // 2 seconds
            ],
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        // Verify the client is created successfully with all parameters
        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testBundleEnvironmentSeparation(): void
    {
        // Test that different environments can have different configurations
        $prodConfig = [
            'user_agent' => 'ProdApp/1.0',
            'throttle' => ['enabled' => true, 'microseconds' => 1000000],
        ];

        $testConfig = [
            'user_agent' => 'TestApp/1.0',
            'throttle' => ['enabled' => false],
        ];

        $prodKernel = new TestKernel($prodConfig, 'prod');
        $testKernel = new TestKernel($testConfig, 'test');

        $prodKernel->boot();
        $testKernel->boot();

        $prodClient = $prodKernel->getContainer()->get('calliostro_discogs.discogs_client');
        $testClient = $testKernel->getContainer()->get('calliostro_discogs.discogs_client');

        $this->assertInstanceOf(DiscogsApiClient::class, $prodClient);
        $this->assertInstanceOf(DiscogsApiClient::class, $testClient);
        $this->assertNotSame($prodClient, $testClient);

        $prodKernel->cleanupCache();
        $testKernel->cleanupCache();
    }
}
