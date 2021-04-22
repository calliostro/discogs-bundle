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
        $this->assertEquals('test', $discogsClient->getHttpClient()->getConfig('headers')['User-Agent']);
    }
}

final class CalliostroDiscogsTestingKernel extends Kernel
{
    private $calliostroDiscogsConfig;

    public function __construct(array $calliostroDiscogsConfig = [])
    {
        $this->calliostroDiscogsConfig = $calliostroDiscogsConfig;

        parent::__construct('test', true);
    }

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

    public function getCacheDir()
    {
        return $this->getProjectDir() . '/var/cache/'.$this->environment.'/'.spl_object_hash($this);
    }
}
