<?php

namespace Calliostro\DiscogsBundle\Tests\Unit\DependencyInjection;

use Calliostro\DiscogsBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testEmptyConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, []);

        $expected = [
            'user_agent' => null,
            'throttle' => [
                'enabled' => true,
                'microseconds' => 1000000,
            ],
        ];

        $this->assertEquals($expected, $config);
    }

    public function testConfigurationWithUserAgent(): void
    {
        $configs = [
            [
                'user_agent' => 'MyApp/1.0',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertEquals('MyApp/1.0', $config['user_agent']);
        $this->assertTrue($config['throttle']['enabled']);
        $this->assertEquals(1000000, $config['throttle']['microseconds']);
    }

    public function testConfigurationWithConsumerCredentials(): void
    {
        $configs = [
            [
                'consumer_key' => 'test_key',
                'consumer_secret' => 'test_secret',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertEquals('test_key', $config['consumer_key']);
        $this->assertEquals('test_secret', $config['consumer_secret']);
        $this->assertArrayNotHasKey('personal_access_token', $config);
    }

    public function testConfigurationWithPersonalAccessToken(): void
    {
        $configs = [
            [
                'personal_access_token' => 'my_token_123',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertEquals('my_token_123', $config['personal_access_token']);
        $this->assertArrayNotHasKey('consumer_key', $config);
        $this->assertArrayNotHasKey('consumer_secret', $config);
    }

    public function testThrottleConfiguration(): void
    {
        $configs = [
            [
                'throttle' => [
                    'enabled' => false,
                    'microseconds' => 500000,
                ],
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertFalse($config['throttle']['enabled']);
        $this->assertEquals(500000, $config['throttle']['microseconds']);
    }

    public function testThrottleEnabledOnlyConfiguration(): void
    {
        $configs = [
            [
                'throttle' => [
                    'enabled' => false,
                ],
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertFalse($config['throttle']['enabled']);
        $this->assertEquals(1000000, $config['throttle']['microseconds']); // Default value
    }

    public function testThrottleMicrosecondsOnlyConfiguration(): void
    {
        $configs = [
            [
                'throttle' => [
                    'microseconds' => 250000,
                ],
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertTrue($config['throttle']['enabled']); // Default value
        $this->assertEquals(250000, $config['throttle']['microseconds']);
    }

    public function testCompleteConfiguration(): void
    {
        $configs = [
            [
                'user_agent' => 'TestApp/2.0',
                'consumer_key' => 'valid_consumer_key_12345',
                'consumer_secret' => 'valid_consumer_secret_12345',
                'personal_access_token' => 'BillieEilishToken2024_123456789',
                'throttle' => [
                    'enabled' => true,
                    'microseconds' => 750000,
                ],
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $expected = [
            'user_agent' => 'TestApp/2.0',
            'consumer_key' => 'valid_consumer_key_12345',
            'consumer_secret' => 'valid_consumer_secret_12345',
            'personal_access_token' => 'BillieEilishToken2024_123456789',
            'throttle' => [
                'enabled' => true,
                'microseconds' => 750000,
            ],
        ];

        $this->assertEquals($expected, $config);
    }

    public function testConfigurationMerging(): void
    {
        $configs = [
            [
                'user_agent' => 'FirstApp/1.0',
                'consumer_key' => 'first_key',
            ],
            [
                'user_agent' => 'SecondApp/2.0',
                'consumer_secret' => 'second_secret',
                'throttle' => [
                    'enabled' => false,
                ],
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        // Second config should override first where present
        $this->assertEquals('SecondApp/2.0', $config['user_agent']);
        $this->assertEquals('first_key', $config['consumer_key']); // From first config
        $this->assertEquals('second_secret', $config['consumer_secret']); // From second config
        $this->assertFalse($config['throttle']['enabled']); // From second config
        $this->assertEquals(1000000, $config['throttle']['microseconds']); // Default value
    }

    public function testTreeBuilderReturnsCorrectRootName(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();

        $this->assertEquals('calliostro_discogs', $treeBuilder->buildTree()->getName());
    }
}
