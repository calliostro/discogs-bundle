<?php

namespace Calliostro\DiscogsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link https://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('calliostro_discogs');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('user_agent')
                    ->defaultValue('CalliostroDiscogsBundle/2.0 +https://github.com/calliostro/discogs-bundle')
                    ->info('Freely selectable and valid HTTP user agent identification (required)')
                ->end()
                ->scalarNode('consumer_key')
                    ->info('Your consumer key (recommended)')
                ->end()
                ->scalarNode('consumer_secret')
                    ->info('Your consumer secret (recommended)')
                ->end()
                ->arrayNode('throttle')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('If activated, a new attempt is made later when the rate limit is reached')
                        ->end()
                        ->integerNode('microseconds')
                            ->defaultValue(1000000)
                            ->info('Number of milliseconds to wait until the next attempt when the rate limit is reached')
                        ->end()
                     ->end()
                ->end()
                ->arrayNode('oauth')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->info('If enabled, full OAuth 1.0a with access token/secret is used')
                        ->end()
                        ->scalarNode('token_provider')
                            ->defaultValue('calliostro_discogs.hwi_oauth_token_provider')
                            ->info('You can create a service implementing OAuthTokenProviderInterface (HWIOAuthBundle is supported by default)')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($a) {
                    $enabled = $a['oauth']['enabled'];
                    $key = isset($a['consumer_key']) && $a['consumer_key'];
                    $secret = isset($a['consumer_secret']) && $a['consumer_secret'];
                    $token = isset($a['oauth']['token_provider']) && $a['oauth']['token_provider'];

                    return $enabled && (! $key || ! $secret || ! $token);
                })
                ->thenInvalid('The option "calliostro_discogs.consumer_key", "calliostro_discogs.consumer_secret" and "calliostro_discogs.oauth.token_provider" are required')
            ->end()
        ;

        return $treeBuilder;
    }
}
