<?php

namespace Calliostro\DiscogsBundle\Tests\Fixtures;

use Calliostro\DiscogsBundle\CalliostroDiscogsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Enhanced test kernel with better cache management and environment separation.
 */
final class TestKernel extends Kernel
{
    /**
     * @var array<string, mixed>
     */
    private array $calliostroDiscogsConfig;

    /**
     * @var array<int, mixed>
     */
    private array $extraBundles;

    /**
     * @param array<string, mixed> $calliostroDiscogsConfig
     * @param string               $environment             Test environment name
     * @param array<int, mixed>    $extraBundles            Additional bundles to register
     */
    public function __construct(
        array $calliostroDiscogsConfig = [],
        string $environment = 'test',
        array $extraBundles = [],
    ) {
        $this->calliostroDiscogsConfig = $calliostroDiscogsConfig;
        $this->extraBundles = $extraBundles;

        parent::__construct($environment, true);
    }

    /**
     * @return array<int, mixed>
     */
    public function registerBundles(): array
    {
        $bundles = [
            new CalliostroDiscogsBundle(),
        ];

        return array_merge($bundles, $this->extraBundles);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            // Load the bundle configuration
            if (!empty($this->calliostroDiscogsConfig)) {
                $container->loadFromExtension('calliostro_discogs', $this->calliostroDiscogsConfig);
            }

            // Add common test services
            $container->setParameter('kernel.secret', 'test_secret');

            // Disable logging in tests to reduce noise
            $container->setParameter('kernel.logs_dir', $this->getLogDir());
        });
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.
            $this->environment.'/'.
            md5(serialize($this->calliostroDiscogsConfig)).'/'.
            spl_object_hash($this);
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/log/'.$this->environment;
    }

    /**
     * Helper method to create a kernel for specific test scenarios.
     */
    public static function createForIntegration(array $config = []): self
    {
        return new self($config, 'integration_test');
    }

    /**
     * Helper method to create a kernel for unit test scenarios.
     */
    public static function createForUnit(array $config = []): self
    {
        return new self($config, 'unit_test');
    }

    /**
     * Helper method to create a kernel for functional test scenarios.
     */
    public static function createForFunctional(array $config = []): self
    {
        return new self($config, 'functional_test');
    }

    /**
     * Cleanup method to remove the test cache after test execution.
     */
    public function cleanupCache(): void
    {
        $cacheDir = $this->getCacheDir();
        if (is_dir($cacheDir)) {
            $this->removeDirectory($cacheDir);
        }
    }

    /**
     * Recursively remove a directory and its contents.
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.\DIRECTORY_SEPARATOR.$file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
