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

    public function testEmptyPersonalAccessTokenNowAllowed(): void
    {
        // This should now pass (no longer fails at compile time)
        $configs = [
            [
                'personal_access_token' => '',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);
        $this->assertEquals('', $config['personal_access_token']);
    }

    public function testWhitespaceOnlyPersonalAccessTokenNowAllowed(): void
    {
        // This should now pass (no longer fails at compile time)
        $configs = [
            [
                'personal_access_token' => '   ',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);
        $this->assertEquals('   ', $config['personal_access_token']);
    }

    public function testShortPersonalAccessTokenNowAllowed(): void
    {
        // This should now pass (validation moved to runtime)
        $configs = [
            [
                'personal_access_token' => 'short',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);
        $this->assertEquals('short', $config['personal_access_token']);
    }

    public function testEmptyConsumerKeyNowAllowed(): void
    {
        // This should now pass (no longer fails at compile time)
        $configs = [
            [
                'consumer_key' => '',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);
        $this->assertEquals('', $config['consumer_key']);
    }

    public function testEmptyConsumerSecretNowAllowed(): void
    {
        // This should now pass (no longer fails at compile time)
        $configs = [
            [
                'consumer_secret' => '',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);
        $this->assertEquals('', $config['consumer_secret']);
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

    public function testEnvironmentVariableSyntaxAllowed(): void
    {
        // Test that environment variable syntax is now allowed
        $configs = [
            [
                'personal_access_token' => '%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%',
                'consumer_key' => '%env(DISCOGS_CONSUMER_KEY)%',
                'consumer_secret' => '%env(DISCOGS_CONSUMER_SECRET)%',
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, $configs);
        $this->assertEquals('%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%', $config['personal_access_token']);
        $this->assertEquals('%env(DISCOGS_CONSUMER_KEY)%', $config['consumer_key']);
        $this->assertEquals('%env(DISCOGS_CONSUMER_SECRET)%', $config['consumer_secret']);
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
