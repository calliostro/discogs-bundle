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
                ->end()
                ->arrayNode('throttle')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('microseconds')
                            ->defaultValue(1000000)
                        ->end()
                     ->end()
                ->end()
                ->arrayNode('oauth')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('consumer_key')->end()
                        ->scalarNode('consumer_secret')->end()
                        ->scalarNode('token_provider')->defaultValue('calliostro_discogs.hwi_oauth_token_provider')->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function($a) {
                            $enabled = $a['enabled'];
                            $key = isset($a['consumer_key']) && $a['consumer_key'];
                            $secret = isset($a['consumer_secret']) && $a['consumer_secret'];
                            $token = isset($a['token_provider']) && $a['token_provider'];

                            return $enabled && (! $key || ! $secret || ! $token);
                        })
                        ->thenInvalid('The option "calliostro_discogs.oauth.consumer_key", "calliostro_discogs.oauth.consumer_secret" and "calliostro_discogs.oauth.token_provider" are required')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
