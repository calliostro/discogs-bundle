<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit\DependencyInjection;

use Calliostro\DiscogsBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    public function testEmptyConfiguration(): void
    {
        $configs = [[]];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertArrayNotHasKey('personal_access_token', $config);
        $this->assertArrayNotHasKey('consumer_key', $config);
        $this->assertArrayNotHasKey('consumer_secret', $config);
        $this->assertNull($config['user_agent']);
        $this->assertNull($config['rate_limiter']);
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
        $this->assertArrayNotHasKey('throttle', $config);
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

    public function testRateLimiterBasicConfiguration(): void
    {
        $configs = [
            [
                'rate_limiter' => 'my_rate_limiter_service',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertEquals('my_rate_limiter_service', $config['rate_limiter']);
        $this->assertArrayNotHasKey('throttle', $config);
    }

    public function testCompleteConfiguration(): void
    {
        $configs = [
            [
                'user_agent' => 'TestApp/2.0',
                'consumer_key' => 'valid_consumer_key_12345',
                'consumer_secret' => 'valid_consumer_secret_12345',
                'personal_access_token' => 'BillieEilishToken2024_123456789',
                'rate_limiter' => 'my_rate_limiter',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertEquals('TestApp/2.0', $config['user_agent']);
        $this->assertEquals('valid_consumer_key_12345', $config['consumer_key']);
        $this->assertEquals('valid_consumer_secret_12345', $config['consumer_secret']);
        $this->assertEquals('BillieEilishToken2024_123456789', $config['personal_access_token']);
        $this->assertEquals('my_rate_limiter', $config['rate_limiter']);
    }

    public function testMultipleConfigurationMerging(): void
    {
        $configs = [
            [
                'user_agent' => 'FirstApp/1.0',
                'consumer_key' => 'first_key',
            ],
            [
                'user_agent' => 'SecondApp/2.0',
                'consumer_secret' => 'second_secret',
                'rate_limiter' => 'my_rate_limiter',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        // Second config should override first where present
        $this->assertEquals('SecondApp/2.0', $config['user_agent']);
        $this->assertEquals('first_key', $config['consumer_key']); // From first config
        $this->assertEquals('second_secret', $config['consumer_secret']); // From second config
        $this->assertEquals('my_rate_limiter', $config['rate_limiter']); // From second config
    }

    public function testRateLimiterConfiguration(): void
    {
        $configs = [
            [
                'rate_limiter' => 'my_rate_limiter_factory',
                'personal_access_token' => 'token123456789', // Must be at least 10 chars
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertEquals('my_rate_limiter_factory', $config['rate_limiter']);
        $this->assertEquals('token123456789', $config['personal_access_token']);
    }

    public function testTreeBuilderReturnsCorrectRootName(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();

        $this->assertEquals('calliostro_discogs', $treeBuilder->buildTree()->getName());
    }

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }
}
