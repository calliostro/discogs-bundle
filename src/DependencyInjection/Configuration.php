<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('calliostro_discogs');
        $rootNode = $treeBuilder->getRootNode();

        // @phpstan-ignore-next-line: Symfony Config Builder has dynamic method resolution
        $rootNode
            ->children()
            ->scalarNode('personal_access_token')
            ->info('Your personal access token (recommended - get from https://www.discogs.com/settings/developers)')
            ->validate()
            ->ifTrue(fn ($v) => \is_string($v) && '' === trim($v))
            ->thenInvalid('Personal access token cannot be empty')
            ->end()
            ->validate()
            ->ifTrue(fn ($v) => \is_string($v) && '' !== trim($v) && \strlen(trim($v)) < 10)
            ->thenInvalid('Personal access token must be at least 10 characters')
            ->end()
            ->end()
            ->scalarNode('consumer_key')
            ->info('Your consumer key (alternative for OAuth applications)')
            ->validate()
            ->ifTrue(fn ($v) => \is_string($v) && '' === trim($v))
            ->thenInvalid('Consumer key cannot be empty')
            ->end()
            ->end()
            ->scalarNode('consumer_secret')
            ->info('Your consumer secret (alternative for OAuth applications)')
            ->validate()
            ->ifTrue(fn ($v) => \is_string($v) && '' === trim($v))
            ->thenInvalid('Consumer secret cannot be empty')
            ->end()
            ->end()
            ->scalarNode('user_agent')
            ->defaultNull()
            ->info('HTTP User-Agent header for API requests (optional)')
            ->validate()
            ->ifTrue(fn ($v) => \is_string($v) && \strlen($v) > 200)
            ->thenInvalid('User-Agent cannot be longer than 200 characters')
            ->end()
            ->end()
            ->scalarNode('rate_limiter')
            ->defaultNull()
            ->info('Symfony RateLimiterFactory service ID for advanced rate limiting (requires symfony/rate-limiter)')
            ->validate()
            ->ifTrue(fn ($v) => \is_string($v) && '' === trim($v))
            ->thenInvalid('Rate limiter service ID cannot be empty')
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
