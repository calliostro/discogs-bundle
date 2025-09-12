<?php

namespace Calliostro\DiscogsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more, see {@link https://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
final class CalliostroDiscogsExtension extends Extension
{
    public function getAlias(): string
    {
        return 'calliostro_discogs';
    }

    /**
     * @throws \Exception When the XML service configuration file cannot be loaded
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // Configure client based on authentication method
        $this->configureClient($container, $config);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureClient(ContainerBuilder $container, array $config): void
    {
        $clientDefinition = $container->getDefinition('calliostro_discogs.discogs_client');

        if (!empty($config['personal_access_token'])) {
            // Personal Access Token authentication (recommended for personal use)
            $clientDefinition->setFactory(['Calliostro\Discogs\ClientFactory', 'createWithPersonalAccessToken']);
            $clientDefinition->setArguments([
                $config['personal_access_token'],
                $this->getClientOptions($container, $config),
            ]);
        } elseif (!empty($config['consumer_key']) && !empty($config['consumer_secret'])) {
            // Consumer credentials authentication
            $clientDefinition->setFactory(['Calliostro\Discogs\ClientFactory', 'createWithConsumerCredentials']);
            $clientDefinition->setArguments([
                $config['consumer_key'],
                $config['consumer_secret'],
                $this->getClientOptions($container, $config),
            ]);
        } else {
            // Anonymous client (rate-limited)
            $clientDefinition->setFactory(['Calliostro\Discogs\ClientFactory', 'create']);
            $clientDefinition->setArguments([
                $this->getClientOptions($container, $config),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function getClientOptions(ContainerBuilder $container, array $config): array
    {
        $options = [];

        // Only set the User-Agent header if explicitly configured
        if (!empty($config['user_agent'])) {
            $options['headers'] = [
                'User-Agent' => $config['user_agent'],
            ];
        }

        // Add throttling handler if enabled
        if ($config['throttle']['enabled']) {
            $throttleHandlerDefinition = $container->getDefinition('calliostro_discogs.throttle_handler_stack');
            $throttleHandlerDefinition->replaceArgument(0, (int) $config['throttle']['microseconds']);
            $options['handler'] = new Reference('calliostro_discogs.throttle_handler_stack');
        }

        return $options;
    }
}
