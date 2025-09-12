<?php

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\DiscogsBundle\DependencyInjection\CalliostroDiscogsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CalliostroDiscogsExtensionTest extends TestCase
{
    public function testLoadWithMinimalConfig(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition('calliostro_discogs.discogs_client'));
    }

    public function testLoadWithThrottleEnabled(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'throttle' => [
                    'enabled' => true,
                    'microseconds' => 500000,
                ],
            ],
        ];

        $extension->load($config, $container);

        // In v4.0.0, throttling is handled differently - no separate subscriber
        $this->assertTrue($container->hasDefinition('calliostro_discogs.throttle_handler_stack'));

        // Verify the client is configured properly
        $this->assertTrue($container->hasDefinition('calliostro_discogs.discogs_client'));
    }

    public function testLoadWithConsumerKeyAndSecretOnly(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'consumer_key' => 'test_key',
                'consumer_secret' => 'test_secret',
            ],
        ];

        $extension->load($config, $container);

        $this->assertTrue($container->hasDefinition('calliostro_discogs.discogs_client'));

        // Check that the client is configured with proper factory method and arguments
        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $clientDefinition->getFactory();
        $this->assertEquals(['Calliostro\Discogs\ClientFactory', 'createWithConsumerCredentials'], $factory);

        // Check that the consumer key and secret are passed as arguments
        $arguments = $clientDefinition->getArguments();
        $this->assertCount(3, $arguments);
        $this->assertEquals('test_key', $arguments[0]);
        $this->assertEquals('test_secret', $arguments[1]);
    }

    public function testLoadWithThrottleDisabled(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'throttle' => [
                    'enabled' => false,
                ],
            ],
        ];

        $extension->load($config, $container);

        $this->assertTrue($container->hasDefinition('calliostro_discogs.discogs_client'));
        // When the throttle is disabled, the basic client factory should be used
        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $clientDefinition->getFactory();
        $this->assertEquals(['Calliostro\Discogs\ClientFactory', 'create'], $factory);
    }

    public function testLoadWithPersonalAccessToken(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'personal_access_token' => 'test_token_123',
            ],
        ];

        $extension->load($config, $container);

        $this->assertTrue($container->hasDefinition('calliostro_discogs.discogs_client'));

        // Check that the client is configured with a personal access token factory
        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $clientDefinition->getFactory();
        $this->assertEquals(['Calliostro\Discogs\ClientFactory', 'createWithPersonalAccessToken'], $factory);

        // Check that the token is passed as the first argument
        $arguments = $clientDefinition->getArguments();
        $this->assertCount(2, $arguments);
        $this->assertEquals('test_token_123', $arguments[0]);
    }

    public function testLoadWithCustomUserAgent(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'user_agent' => 'CustomAgent/1.0',
            ],
        ];

        $extension->load($config, $container);

        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $clientDefinition->getFactory();
        $this->assertEquals(['Calliostro\Discogs\ClientFactory', 'create'], $factory);

        $arguments = $clientDefinition->getArguments();
        $this->assertIsArray($arguments[0]);
        $this->assertArrayHasKey('headers', $arguments[0]);
        $this->assertEquals('CustomAgent/1.0', $arguments[0]['headers']['User-Agent']);
    }

    public function testLoadWithPersonalAccessTokenAndUserAgent(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'personal_access_token' => 'test_token_123',
                'user_agent' => 'TestApp/1.0',
            ],
        ];

        $extension->load($config, $container);

        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');
        $factory = $clientDefinition->getFactory();
        $this->assertEquals(['Calliostro\Discogs\ClientFactory', 'createWithPersonalAccessToken'], $factory);

        // Check that arguments include token and options
        $arguments = $clientDefinition->getArguments();
        $this->assertCount(2, $arguments); // Token + options
        $this->assertEquals('test_token_123', $arguments[0]);
        $this->assertIsArray($arguments[1]); // Options

        // Check that user_agent is embedded in options headers
        $options = $arguments[1];
        $this->assertArrayHasKey('headers', $options);
        $this->assertArrayHasKey('User-Agent', $options['headers']);
        $this->assertEquals('TestApp/1.0', $options['headers']['User-Agent']);
    }
}
