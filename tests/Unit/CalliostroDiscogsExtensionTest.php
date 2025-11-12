<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\DiscogsBundle\DependencyInjection\CalliostroDiscogsExtension;

final class CalliostroDiscogsExtensionTest extends UnitTestCase
{
    public function testLoadWithMinimalConfig(): void
    {
        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $extension->load([], $container);

        $this->assertDefinitionExists($container, 'calliostro_discogs.discogs_client');
    }

    public function testLoadWithRateLimiter(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'rate_limiter' => 'my_rate_limiter_service',
            ],
        ];

        $extension->load($config, $container);

        // Should create rate limiter middleware and handler stack
        $this->assertDefinitionExists($container, 'calliostro_discogs.rate_limiter_middleware');
        $this->assertDefinitionExists($container, 'calliostro_discogs.rate_limiter_handler_stack');

        // Verify the client is configured properly
        $this->assertDefinitionExists($container, 'calliostro_discogs.discogs_client');
    }

    /**
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function testLoadWithRateLimiterWhenComponentNotAvailable(): void
    {
        // This test runs in a separate process where we can mock the class_exists function
        // by using PHP's namespace fallback behavior

        // Create a mock function in our namespace that overrides class_exists
        eval('
            namespace Calliostro\DiscogsBundle\DependencyInjection;
            function class_exists($className) {
                if ($className === "Symfony\\\\Component\\\\RateLimiter\\\\RateLimiterFactory") {
                    return false; // Simulate missing component
                }
                return \\class_exists($className);
            }
        ');

        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'rate_limiter' => 'my_rate_limiter_service',
            ],
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('To use the rate_limiter configuration, you must install symfony/rate-limiter. Run: composer require symfony/rate-limiter');

        $extension->load($config, $container);
    }

    public function testLoadWithConsumerKeyAndSecretOnly(): void
    {
        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'consumer_key' => 'test_key',
                'consumer_secret' => 'test_secret',
            ],
        ];

        $extension->load($config, $container);

        // Our new architecture always uses the same factory method
        $this->assertTrue($container->hasDefinition('calliostro_discogs.client_factory'));
        $definition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $definition->getFactory();
        $this->assertEquals('createClient', $factory[1]);
        $this->assertDefinitionArgumentCount($container, 'calliostro_discogs.discogs_client', 4);
        // Check that consumer credentials are at the correct positions
        $this->assertDefinitionArgumentEquals($container, 'calliostro_discogs.discogs_client', 1, 'test_key');
        $this->assertDefinitionArgumentEquals($container, 'calliostro_discogs.discogs_client', 2, 'test_secret');
    }

    public function testLoadWithoutRateLimiter(): void
    {
        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [[]]; // Empty configuration

        $extension->load($config, $container);

        // Our new architecture always uses the same factory method
        $this->assertTrue($container->hasDefinition('calliostro_discogs.client_factory'));
        $definition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $definition->getFactory();
        $this->assertEquals('createClient', $factory[1]);
    }

    public function testLoadWithPersonalAccessToken(): void
    {
        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'personal_access_token' => 'test_token_123',
            ],
        ];

        $extension->load($config, $container);

        // Our new architecture always uses the same factory method
        $this->assertTrue($container->hasDefinition('calliostro_discogs.client_factory'));
        $definition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $definition->getFactory();
        $this->assertEquals('createClient', $factory[1]);
        $this->assertDefinitionArgumentCount($container, 'calliostro_discogs.discogs_client', 4);
        $this->assertDefinitionArgumentEquals($container, 'calliostro_discogs.discogs_client', 0, 'test_token_123');
    }

    public function testLoadWithCustomUserAgent(): void
    {
        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'user_agent' => 'CustomAgent/1.0',
            ],
        ];

        $extension->load($config, $container);

        // Our new architecture always uses the same factory method
        $this->assertTrue($container->hasDefinition('calliostro_discogs.client_factory'));
        $definition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $definition->getFactory();
        $this->assertEquals('createClient', $factory[1]);
        $arguments = $definition->getArguments();
        $options = $arguments[3]; // Options are at index 3
        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertEquals('CustomAgent/1.0', $options['headers']['User-Agent']);
    }

    public function testLoadWithPersonalAccessTokenAndUserAgent(): void
    {
        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'personal_access_token' => 'test_token_123',
                'user_agent' => 'TestApp/1.0',
            ],
        ];

        $extension->load($config, $container);

        // Verify our new factory approach
        $this->assertTrue($container->hasDefinition('calliostro_discogs.client_factory'));
        $this->assertDefinitionArgumentCount($container, 'calliostro_discogs.discogs_client', 4);
        $this->assertDefinitionArgumentEquals($container, 'calliostro_discogs.discogs_client', 0, 'test_token_123');

        $definition = $container->getDefinition('calliostro_discogs.discogs_client');
        $arguments = $definition->getArguments();
        $options = $arguments[3]; // Options are now at index 3
        $this->assertArrayHasKey('headers', $options);
        $this->assertEquals('TestApp/1.0', $options['headers']['User-Agent']);
    }

    public function testRateLimiterIntegration(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        $container = $this->createContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'rate_limiter' => 'my_rate_limiter',
                'personal_access_token' => 'test_token',
            ],
        ];

        $extension->load($config, $container);

        // Should create rate limiter services
        $this->assertTrue($container->hasDefinition('calliostro_discogs.rate_limiter_handler_stack'));
        $this->assertTrue($container->hasDefinition('calliostro_discogs.rate_limiter_middleware'));

        $definition = $container->getDefinition('calliostro_discogs.discogs_client');
        $arguments = $definition->getArguments();
        $options = $arguments[3] ?? []; // Fourth argument is now the options array

        // Should have handler option pointing to rate limiter stack
        $this->assertArrayHasKey('handler', $options);
    }
}
