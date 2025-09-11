<?php

namespace Calliostro\DiscogsBundle\Tests;

use Calliostro\DiscogsBundle\DependencyInjection\CalliostroDiscogsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CalliostroDiscogsExtensionTest extends TestCase
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

        $this->assertTrue($container->hasDefinition('calliostro_discogs.throttle_subscriber'));
        $this->assertTrue($container->hasDefinition('calliostro_discogs.throttle_handler_stack'));
    }

    public function testLoadWithOAuthEnabled(): void
    {
        $container = new ContainerBuilder();
        $extension = new CalliostroDiscogsExtension();

        $config = [
            [
                'oauth' => [
                    'enabled' => true,
                    'token_provider' => 'app.oauth_token_provider',
                ],
                'consumer_key' => 'test_key',
                'consumer_secret' => 'test_secret',
            ],
        ];

        $extension->load($config, $container);

        $this->assertTrue($container->hasDefinition('calliostro_discogs.subscriber.oauth'));
        $this->assertTrue($container->hasDefinition('calliostro_discogs.oauth_handler_stack'));
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

        // Check that the Authorization header is set correctly
        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');
        $arguments = $clientDefinition->getArguments();
        $this->assertArrayHasKey('headers', $arguments[0]);
        $this->assertArrayHasKey('Authorization', $arguments[0]['headers']);
        $this->assertEquals(
            'Discogs key=test_key, secret=test_secret',
            $arguments[0]['headers']['Authorization']
        );
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
        // When the throttle is disabled, no throttle handler should be configured
        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');
        $arguments = $clientDefinition->getArguments();
        $this->assertArrayNotHasKey('handler', $arguments[0]);
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
        $arguments = $clientDefinition->getArguments();
        $this->assertArrayHasKey('headers', $arguments[0]);
        $this->assertArrayHasKey('User-Agent', $arguments[0]['headers']);
        $this->assertEquals('CustomAgent/1.0', $arguments[0]['headers']['User-Agent']);
    }
}
