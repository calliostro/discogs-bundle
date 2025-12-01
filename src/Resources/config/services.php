<?php

declare(strict_types=1);

use Calliostro\Discogs\DiscogsClient;
use Calliostro\Discogs\DiscogsClientFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Main Discogs API Client
    $services->set('calliostro_discogs.discogs_client', DiscogsClient::class)
        ->public()
        ->factory([DiscogsClientFactory::class, 'create'])
        ->args([[]]);

    // Primary alias for autowiring (Symfony 7.4+ and 8.0+)
    // Must be explicitly private to match XML configuration
    $services->alias(DiscogsClient::class, 'calliostro_discogs.discogs_client')
        ->private();
};
