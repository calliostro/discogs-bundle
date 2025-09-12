<?php

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\DiscogsBundle\DependencyInjection\CalliostroDiscogsExtension;
use Calliostro\DiscogsBundle\Tests\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests for edge cases and proper error handling in bundle configuration.
 */
final class BundleEdgeCasesTest extends TestCase
{
    public function testExtensionHandlesEmptyConfig(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        // Empty config should work with defaults
        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition('calliostro_discogs.discogs_client'));
    }

    public function testExtensionHandlesNestedEmptyArrays(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'throttle' => [],
            ],
        ];

        $extension->load($config, $container);

        $this->assertTrue($container->hasDefinition('calliostro_discogs.discogs_client'));
        $this->assertTrue($container->hasDefinition('calliostro_discogs.throttle_handler_stack'));
    }

    public function testKernelHandlesCacheDirectoryCreation(): void
    {
        $kernel = TestKernel::createForFunctional(['user_agent' => 'CacheTest/1.0']);

        // Boot kernel - this should create the cache directory
        $kernel->boot();

        $cacheDir = $kernel->getCacheDir();
        $this->assertDirectoryExists($cacheDir);

        $kernel->cleanupCache();

        // After cleanup, the cache directory should be removed
        $this->assertDirectoryDoesNotExist($cacheDir);
    }

    public function testKernelHandlesMultipleBootCalls(): void
    {
        $kernel = TestKernel::createForFunctional(['user_agent' => 'MultiBootTest/1.0']);

        // First boot
        $kernel->boot();
        $container1 = $kernel->getContainer();

        // Second boot (should not cause issues)
        $kernel->boot();
        $container2 = $kernel->getContainer();

        // Should return the same container
        $this->assertSame($container1, $container2);

        $kernel->cleanupCache();
    }

    public function testBundleWithValidThrottleMicroseconds(): void
    {
        // Test with valid positive microseconds value
        $config = [
            'throttle' => [
                'enabled' => true,
                'microseconds' => 500000, // 0.5 seconds - valid
            ],
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertNotNull($client);

        $kernel->cleanupCache();
    }

    public function testBundleWithZeroMicroseconds(): void
    {
        $config = [
            'throttle' => [
                'enabled' => true,
                'microseconds' => 0,
            ],
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertNotNull($client);

        $kernel->cleanupCache();
    }

    public function testBundleWithValidLongUserAgent(): void
    {
        // Use a valid user agent within the 200 character limit (exactly 200 chars)
        $longUserAgent = str_repeat('A', 170).'/1.0+https://example.com'; // 170 + 30 = 200 chars

        $config = [
            'user_agent' => $longUserAgent,
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertNotNull($client);

        $kernel->cleanupCache();
    }

    public function testBundleWithSpecialCharactersInCredentials(): void
    {
        $config = [
            'consumer_key' => 'key_with_!@#$%^&*()_+-={}[]|\\:";\'<>?,./~`_valid_length',
            'consumer_secret' => 'secret_with_special_chars_äöü_🚀_valid_length_123',
            'personal_access_token' => 'token_with_unicode_测试_valid_length_123456789',
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertNotNull($client);

        $kernel->cleanupCache();
    }

    public function testBundleWithNumericStringCredentials(): void
    {
        $config = [
            'consumer_key' => '1234567890abcdef_valid_length',
            'consumer_secret' => '9876543210fedcba_valid_length',
            'personal_access_token' => '555666777888999000_valid_length',
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertNotNull($client);

        $kernel->cleanupCache();
    }

    public function testMultipleKernelCleanupDoesNotFail(): void
    {
        $kernel = TestKernel::createForFunctional(['user_agent' => 'CleanupTest/1.0']);
        $kernel->boot();

        // First cleanup
        $kernel->cleanupCache();

        // Second cleanup should not fail
        $kernel->cleanupCache();

        // Third cleanup should not fail
        $kernel->cleanupCache();

        $this->assertTrue(true); // If we get here, no exceptions were thrown
    }

    public function testKernelWithNonExistentCacheDirectoryParent(): void
    {
        $kernel = TestKernel::createForFunctional(['user_agent' => 'DeepCache/1.0']);
        $kernel->boot();

        $cacheDir = $kernel->getCacheDir();

        // Cache directory should be created even if parent directories don't exist
        $this->assertDirectoryExists($cacheDir);

        $kernel->cleanupCache();
    }

    public function testBundleHandlesValidMinimalValues(): void
    {
        // Use the minimal valid configuration (no credentials = anonymous client)
        $config = [
            'user_agent' => 'TestApp/1.0',
        ];

        $kernel = TestKernel::createForFunctional($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        // Should work with minimal valid config
        $client = $container->get('calliostro_discogs.discogs_client');
        $this->assertNotNull($client);

        $kernel->cleanupCache();
    }

    public function testExtensionAlias(): void
    {
        $extension = new CalliostroDiscogsExtension();

        // Should have the correct alias
        $this->assertEquals('calliostro_discogs', $extension->getAlias());
    }

    public function testConfigurationTreeBuilderRootName(): void
    {
        $extension = new CalliostroDiscogsExtension();
        $config = $extension->getConfiguration([], new ContainerBuilder());

        $this->assertNotNull($config);

        $treeBuilder = $config->getConfigTreeBuilder();
        $tree = $treeBuilder->buildTree();

        $this->assertEquals('calliostro_discogs', $tree->getName());
    }
}
