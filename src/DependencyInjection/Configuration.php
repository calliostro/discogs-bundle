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

        $rootNode
            ->children()
            ->scalarNode('personal_access_token')
            ->info('Your personal access token (recommended - get from https://www.discogs.com/settings/developers)')
            ->end()
            ->scalarNode('consumer_key')
            ->info('Your consumer key (alternative for OAuth applications)')
            ->end()
            ->scalarNode('consumer_secret')
            ->info('Your consumer secret (alternative for OAuth applications)')
            ->end()
            ->scalarNode('user_agent')
            ->defaultNull()
            ->info('HTTP User-Agent header for API requests (optional)')
            ->validate()
            ->ifTrue(static fn ($v) => \is_string($v) && \strlen($v) > 200)
            ->thenInvalid('User-Agent cannot be longer than 200 characters')
            ->end()
            ->end()
            ->booleanNode('auto_retry')
            ->defaultTrue()
            ->info('Automatically wait and retry on 429 and 503 responses')
            ->end()
            ->integerNode('max_retries')
            ->defaultValue(3)
            ->min(0)
            ->max(10)
            ->info('Maximum number of retry attempts for 429/503 responses')
            ->end()
            ->scalarNode('rate_limiter')
            ->defaultNull()
            ->info('Symfony RateLimiterFactory service ID for advanced rate limiting (requires symfony/rate-limiter)')
            ->validate()
            ->ifTrue(static fn ($v) => \is_string($v) && '' === trim($v))
            ->thenInvalid('Rate limiter service ID cannot be empty')
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
