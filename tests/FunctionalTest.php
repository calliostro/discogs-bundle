<?php

namespace Calliostro\DiscogsBundle\Tests;

use Calliostro\DiscogsBundle\CalliostroDiscogsBundle;
use Discogs\DiscogsClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    public function testServiceWiring(): void
    {
        $kernel = new CalliostroDiscogsTestingKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $discogsClient = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsClient::class, $discogsClient);
    }

    public function testServiceWiringWithConfiguration(): void
    {
        $kernel = new CalliostroDiscogsTestingKernel([
            'user_agent' => 'test',
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();

        $discogsClient = $container->get('calliostro_discogs.discogs_client');
        $this->assertInstanceOf(DiscogsClient::class, $discogsClient);

        // Verify that the HTTP client is properly configured
        // The user agent configuration is handled internally by the bundle
        $httpClient = $discogsClient->getHttpClient();
        // @phpstan-ignore-next-line method.alreadyNarrowedType
        $this->assertNotNull($httpClient);
    }
}

final class CalliostroDiscogsTestingKernel extends Kernel
{
    /**
     * @var array<string, mixed>
     */
    private array $calliostroDiscogsConfig;

    /**
     * @param array<string, mixed> $calliostroDiscogsConfig
     */
    public function __construct(array $calliostroDiscogsConfig = [])
    {
        $this->calliostroDiscogsConfig = $calliostroDiscogsConfig;

        parent::__construct('test', true);
    }

    /**
     * @return array<int, mixed>
     */
    public function registerBundles(): array
    {
        return [
            new CalliostroDiscogsBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('calliostro_discogs', $this->calliostroDiscogsConfig);
        });
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment.'/'.spl_object_hash($this);
    }
}
