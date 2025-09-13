<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\DiscogsBundle\Tests\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for unit tests with common helper methods.
 */
abstract class UnitTestCase extends TestCase
{
    /**
     * @var TestKernel[]
     */
    private array $kernels = [];

    /**
     * Create a container builder for dependency injection tests.
     */
    protected function createContainerBuilder(): ContainerBuilder
    {
        return new ContainerBuilder();
    }

    /**
     * Assert that a service has the expected type.
     */
    protected function assertServiceInstanceOf(ContainerInterface $container, string $serviceId, string $expectedClass): void
    {
        $this->assertServiceExists($container, $serviceId);
        $service = $container->get($serviceId);
        $this->assertInstanceOf($expectedClass, $service);
    }

    /**
     * Assert that a service exists in the container.
     */
    protected function assertServiceExists(ContainerInterface $container, string $serviceId): void
    {
        $this->assertTrue($container->has($serviceId), "Service '{$serviceId}' should exist in container");
    }

    /**
     * Assert container definition has expected factory.
     *
     * @param array<string, string> $expectedFactory
     */
    protected function assertDefinitionHasFactory(ContainerBuilder $container, string $serviceId, array $expectedFactory): void
    {
        $this->assertDefinitionExists($container, $serviceId);
        $definition = $container->getDefinition($serviceId);
        $this->assertEquals($expectedFactory, $definition->getFactory());
    }

    /**
     * Assert container definition exists in a build-time container.
     */
    protected function assertDefinitionExists(ContainerBuilder $container, string $serviceId): void
    {
        $this->assertTrue($container->hasDefinition($serviceId), "Definition '{$serviceId}' should exist in container");
    }

    /**
     * Assert definition has the expected number of arguments.
     */
    protected function assertDefinitionArgumentCount(ContainerBuilder $container, string $serviceId, int $expectedCount): void
    {
        $this->assertDefinitionExists($container, $serviceId);
        $definition = $container->getDefinition($serviceId);
        $this->assertCount($expectedCount, $definition->getArguments());
    }

    /**
     * Assert definition argument has expected value.
     */
    protected function assertDefinitionArgumentEquals(ContainerBuilder $container, string $serviceId, int $argumentIndex, $expectedValue): void
    {
        $this->assertDefinitionExists($container, $serviceId);
        $definition = $container->getDefinition($serviceId);
        $arguments = $definition->getArguments();
        $this->assertArrayHasKey($argumentIndex, $arguments);
        $this->assertEquals($expectedValue, $arguments[$argumentIndex]);
    }

    /**
     * Boot a kernel and return its container, with automatic cleanup.
     *
     * @param array<string, mixed> $config
     */
    protected function bootKernelAndGetContainer(array $config = []): ContainerInterface
    {
        $kernel = $this->createTestKernel($config);
        $kernel->boot();
        $this->kernels[] = $kernel;

        return $kernel->getContainer();
    }

    /**
     * Create a test kernel with the given configuration.
     *
     * @param array<string, mixed> $config
     */
    protected function createTestKernel(array $config = []): TestKernel
    {
        return TestKernel::createForFunctional($config);
    }

    /**
     * Cleanup kernel after test.
     */
    protected function cleanupKernel(TestKernel $kernel): void
    {
        $kernel->cleanupCache();
    }

    protected function tearDown(): void
    {
        foreach ($this->kernels as $kernel) {
            $kernel->cleanupCache();
        }
        $this->kernels = [];
        parent::tearDown();
    }
}
