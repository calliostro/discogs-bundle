<?php

namespace Calliostro\DiscogsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link https://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
final class CalliostroDiscogsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $params = [
            'headers' => ['User-Agent' => $config['user_agent']]
        ];

        $this->configureThrottling($container, $config, $params);
        $this->configureOAuth($container, $config, $params, $loader);

        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');
        $clientDefinition->replaceArgument(0, $params);
    }

    private function configureThrottling(ContainerBuilder $container, array $config, array &$params): void
    {
        if (!$config['throttle']['enabled']) {
            return;
        }

        $throttleDefinition = $container->getDefinition('calliostro_discogs.throttle_subscriber');
        $throttleDefinition->replaceArgument(0, $config['throttle']['microseconds']);

        $throttleHandlerDefinition = $container->getDefinition('calliostro_discogs.throttle_handler_stack');
        $throttleHandlerDefinition->replaceArgument(0, new Reference('calliostro_discogs.throttle_subscriber'));

        $params['handler'] = new Reference('calliostro_discogs.throttle_handler_stack');
    }

    private function configureOAuth(ContainerBuilder $container, array $config, array &$params, Loader\XmlFileLoader $loader): void
    {
        if ($config['oauth']['enabled']) {
            $loader->load('oauth.xml');

            $subscriber = $container->getDefinition('calliostro_discogs.subscriber.oauth');
            $subscriber->replaceArgument(0, new Reference($config['oauth']['token_provider']));
            $subscriber->replaceArgument(1, $config['consumer_key']);
            $subscriber->replaceArgument(2, $config['consumer_secret']);

            $oauthHandlerDefinition = $container->getDefinition('calliostro_discogs.oauth_handler_stack');
            $oauthHandlerDefinition->replaceArgument(0, new Reference('calliostro_discogs.subscriber.oauth'));

            $params['handler'] = new Reference('calliostro_discogs.oauth_handler_stack');
        } elseif (isset($config['consumer_key'], $config['consumer_secret'])) {
            $params['headers']['Authorization'] = sprintf(
                'Discogs key=%s, secret=%s', 
                $config['consumer_key'], 
                $config['consumer_secret']
            );
        }
    }
}
