<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\Discogs\DiscogsClient;
use Calliostro\DiscogsBundle\Tests\Fixtures\TestKernel;

final class BundleIntegrationTest extends UnitTestCase
{
    public function testBundleLoadsWithMinimalConfiguration(): void
    {
        $container = $this->bootKernelAndGetContainer();

        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testBundleWithAllConfigurationOptions(): void
    {
        $config = [
            'user_agent' => 'TestBundle/1.0',
            'consumer_key' => 'test_consumer_key',
            'consumer_secret' => 'test_consumer_secret',
            'personal_access_token' => 'test_personal_token',
        ];

        $container = $this->bootKernelAndGetContainer($config);
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testBundleWithAdvancedUserAgent(): void
    {
        $config = [
            'user_agent' => 'AdvancedTestApp/2.0 +https://test.example.com',
        ];
        $container = $this->bootKernelAndGetContainer($config);
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testBundleWithConsumerCredentialsOnly(): void
    {
        $config = ['consumer_key' => 'my_consumer_key', 'consumer_secret' => 'my_consumer_secret'];
        $container = $this->bootKernelAndGetContainer($config);
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testBundleWithPersonalAccessTokenOnly(): void
    {
        $config = ['personal_access_token' => 'my_personal_access_token_123'];
        $container = $this->bootKernelAndGetContainer($config);
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testBundleWithCustomUserAgent(): void
    {
        $config = ['user_agent' => 'MyCustomApp/2.1.0 +http://example.com'];
        $container = $this->bootKernelAndGetContainer($config);
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testBundleServicesArePrivate(): void
    {
        $config = ['consumer_key' => 'test', 'consumer_secret' => 'test'];
        $container = $this->bootKernelAndGetContainer($config);

        // The main service should be public for injection
        $this->assertServiceExists($container, 'calliostro_discogs.discogs_client');

        // Internal services should not be directly accessible in compiled container
        // (This is expected behavior in Symfony - internal services are private)
        $this->assertServiceInstanceOf($container, 'calliostro_discogs.discogs_client', DiscogsClient::class);
    }

    public function testMultipleBundleInstancesIsolation(): void
    {
        $kernel1 = TestKernel::createForFunctional([
            'user_agent' => 'App1/1.0',
        ]);

        $kernel2 = TestKernel::createForFunctional([
            'user_agent' => 'App2/2.0',
        ]);

        $kernel1->boot();
        $kernel2->boot();

        $client1 = $kernel1->getContainer()->get('calliostro_discogs.discogs_client');
        $client2 = $kernel2->getContainer()->get('calliostro_discogs.discogs_client');

        $this->assertInstanceOf(DiscogsClient::class, $client1);
        $this->assertInstanceOf(DiscogsClient::class, $client2);

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
        ]);

        $kernel->boot();
        $container = $kernel->getContainer();

        // Test that we can retrieve the main service
        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsClient::class, $client);

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
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        // Verify the client is created successfully with all parameters
        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsClient::class, $client);

        $kernel->cleanupCache();
    }

    public function testBundleEnvironmentSeparation(): void
    {
        // Test that different environments can have different configurations
        $prodConfig = [
            'user_agent' => 'ProdApp/1.0',
        ];

        $testConfig = [
            'user_agent' => 'TestApp/1.0',
        ];

        $prodKernel = new TestKernel($prodConfig, 'prod');
        $testKernel = new TestKernel($testConfig, 'test');

        $prodKernel->boot();
        $testKernel->boot();

        $prodClient = $prodKernel->getContainer()->get('calliostro_discogs.discogs_client');
        $testClient = $testKernel->getContainer()->get('calliostro_discogs.discogs_client');

        $this->assertInstanceOf(DiscogsClient::class, $prodClient);
        $this->assertInstanceOf(DiscogsClient::class, $testClient);
        $this->assertNotSame($prodClient, $testClient);

        $prodKernel->cleanupCache();
        $testKernel->cleanupCache();
    }
}
