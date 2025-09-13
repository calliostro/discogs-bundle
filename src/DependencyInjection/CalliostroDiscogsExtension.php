<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

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
            $clientDefinition->setFactory(['Calliostro\\Discogs\\DiscogsClientFactory', 'createWithPersonalAccessToken']);
            $clientDefinition->setArguments([
                $config['personal_access_token'],
                $this->getClientOptions($container, $config),
            ]);
        } elseif (!empty($config['consumer_key']) && !empty($config['consumer_secret'])) {
            // Consumer credentials authentication
            $clientDefinition->setFactory(['Calliostro\\Discogs\\DiscogsClientFactory', 'createWithConsumerCredentials']);
            $clientDefinition->setArguments([
                $config['consumer_key'],
                $config['consumer_secret'],
                $this->getClientOptions($container, $config),
            ]);
        } else {
            // Anonymous client (rate-limited)
            $clientDefinition->setFactory(['Calliostro\\Discogs\\DiscogsClientFactory', 'create']);
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
            $options['headers'] = ['User-Agent' => $config['user_agent']];
        }

        // Configure rate limiting if requested
        if (!empty($config['rate_limiter'])) {
            $this->configureSymfonyRateLimiter($container, $config['rate_limiter'], $options);
        }

        return $options;
    }

    /**
     * Configure Symfony Rate Limiter integration.
     *
     * @param array<string, mixed> &$options
     */
    private function configureSymfonyRateLimiter(ContainerBuilder $container, string $rateLimiterService, array &$options): void
    {
        // Check if the symfony/rate-limiter component is available
        if (!$this->isRateLimiterAvailable()) {
            throw new \LogicException('To use the rate_limiter configuration, you must install symfony/rate-limiter. Run: composer require symfony/rate-limiter');
        }

        // Create the rate limiter middleware service
        $middlewareDefinition = $container->register('calliostro_discogs.rate_limiter_middleware', 'Calliostro\\DiscogsBundle\\Middleware\\RateLimiterMiddleware');
        $middlewareDefinition->setArguments([
            new Reference($rateLimiterService),
            'discogs_api', // Default limiter key
        ]);

        // Create a handler stack with the rate limiter middleware
        $handlerDefinition = $container->register('calliostro_discogs.rate_limiter_handler_stack', 'GuzzleHttp\\HandlerStack');
        $handlerDefinition->setFactory(['GuzzleHttp\\HandlerStack', 'create']);
        $handlerDefinition->addMethodCall('push', [
            new Reference('calliostro_discogs.rate_limiter_middleware'),
            'rate_limiter',
        ]);

        $options['handler'] = new Reference('calliostro_discogs.rate_limiter_handler_stack');
    }

    /**
     * Check if the symfony/rate-limiter component is available.
     * This method is protected to allow testing.
     */
    protected function isRateLimiterAvailable(): bool
    {
        return class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory');
    }
}
