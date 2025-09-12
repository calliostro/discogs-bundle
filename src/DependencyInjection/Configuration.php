<?php

namespace Calliostro\DiscogsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more, see {@link https://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
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
                ->arrayNode('throttle')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Rate limiting - retries HTTP 429 with exponential backoff')
                        ->end()
                        ->integerNode('microseconds')
                            ->defaultValue(1000000)
                            ->info('Number of microseconds to wait until the next attempt when rate limit is reached')
                            ->validate()
                                ->ifTrue(fn ($v) => $v < 0)
                                ->thenInvalid('Throttle microseconds must be a positive integer')
                            ->end()
                            ->validate()
                                ->ifTrue(fn ($v) => $v > 60000000) // 60 seconds max
                                ->thenInvalid('Throttle microseconds cannot exceed 60 seconds (60000000 microseconds)')
                            ->end()
                        ->end()
                     ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
