<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\DiscogsBundle\CalliostroDiscogsBundle;
use Calliostro\DiscogsBundle\DependencyInjection\CalliostroDiscogsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class CalliostroDiscogsBundleTest extends UnitTestCase
{
    public function testGetPath(): void
    {
        $bundle = new CalliostroDiscogsBundle();

        $path = $bundle->getPath();

        // The bundle path should point to the root directory (parent of src)
        $this->assertStringContainsString('discogs-bundle', $path);
        $this->assertDirectoryExists($path);
        $this->assertFileExists($path.'/src/CalliostroDiscogsBundle.php');
    }

    public function testGetContainerExtensionReturnsValidExtension(): void
    {
        $bundle = new CalliostroDiscogsBundle();
        $extension = $bundle->getContainerExtension();

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExtensionInterface::class, $extension);
        $this->assertInstanceOf(CalliostroDiscogsExtension::class, $extension);
    }

    public function testGetContainerExtensionReturnsSameInstanceOnMultipleCalls(): void
    {
        $bundle = new CalliostroDiscogsBundle();

        $extension1 = $bundle->getContainerExtension();
        $extension2 = $bundle->getContainerExtension();

        // Should return the same instance (lazy initialization)
        $this->assertSame($extension1, $extension2);
        $this->assertEquals('calliostro_discogs', $extension1->getAlias());
    }

    public function testBundleIsProperSymfonyBundle(): void
    {
        $bundle = new CalliostroDiscogsBundle();

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(CalliostroDiscogsBundle::class, $bundle);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(\Symfony\Component\HttpKernel\Bundle\Bundle::class, $bundle);
    }
}
