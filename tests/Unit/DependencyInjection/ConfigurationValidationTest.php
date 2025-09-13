<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit\DependencyInjection;

use Calliostro\DiscogsBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationValidationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    public function testEmptyPersonalAccessTokenFails(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Personal access token cannot be empty');

        $configs = [
            [
                'personal_access_token' => '',
            ],
        ];

        $this->processor->processConfiguration($this->configuration, $configs);
    }

    public function testWhitespaceOnlyPersonalAccessTokenFails(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Personal access token cannot be empty');

        $configs = [
            [
                'personal_access_token' => '   ',
            ],
        ];

        $this->processor->processConfiguration($this->configuration, $configs);
    }

    public function testShortPersonalAccessTokenFails(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Personal access token must be at least 10 characters');

        $configs = [
            [
                'personal_access_token' => 'short',
            ],
        ];

        $this->processor->processConfiguration($this->configuration, $configs);
    }

    public function testEmptyConsumerKeyFails(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Consumer key cannot be empty');

        $configs = [
            [
                'consumer_key' => '',
            ],
        ];

        $this->processor->processConfiguration($this->configuration, $configs);
    }

    public function testEmptyConsumerSecretFails(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Consumer secret cannot be empty');

        $configs = [
            [
                'consumer_secret' => '',
            ],
        ];

        $this->processor->processConfiguration($this->configuration, $configs);
    }

    public function testTooLongUserAgentFails(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('User-Agent cannot be longer than 200 characters');

        $configs = [
            [
                'user_agent' => str_repeat('A', 201),
            ],
        ];

        $this->processor->processConfiguration($this->configuration, $configs);
    }

    public function testValidConfiguration(): void
    {
        $configs = [
            [
                'personal_access_token' => 'BillieEilishFan2024Token123456789',
                'user_agent' => 'MyMusicApp/2.0 +https://example.com',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);

        $this->assertEquals('BillieEilishFan2024Token123456789', $config['personal_access_token']);
        $this->assertEquals('MyMusicApp/2.0 +https://example.com', $config['user_agent']);
    }

    public function testArrayAsScalarValue(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $configs = [
            [
                'personal_access_token' => ['invalid' => 'array'],
            ],
        ];

        $this->processor->processConfiguration($this->configuration, $configs);
    }

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }
}
