<?php

namespace Calliostro\DiscogsBundle\Tests\Integration;

use Calliostro\Discogs\DiscogsApiClient;
use Calliostro\DiscogsBundle\Tests\Fixtures\TestKernel;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

/**
 * Base class for Bundle integration tests that make real API calls.
 *
 * Provides automatic rate limiting protection and retry logic
 * to prevent CI/CD pipeline failures due to API throttling
 */
abstract class IntegrationTestCase extends TestCase
{
    protected DiscogsApiClient $client;

    /**
     * Create a test kernel with the given configuration.
     *
     * Note: After kernel->boot(), getContainer() returns a compiled container
     * (runtime container) which only has has()/get() methods, not hasDefinition().
     * The ContainerBuilder (build-time) gets compiled away.
     *
     * @param array<string, mixed> $config
     */
    protected function createKernel(array $config = []): TestKernel
    {
        return TestKernel::createForIntegration($config);
    }

    /**
     * Override PHPUnit's runTest to add automatic retry on rate limiting
     * This uses reflection to access the private runTest method.
     *
     * @throws \ReflectionException If reflection operations fail
     */
    protected function runTest(): mixed
    {
        $maxRetries = 2;
        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                // Use reflection to call the private runTest method
                $reflection = new \ReflectionClass(parent::class);
                $method = $reflection->getMethod('runTest');
                /* @noinspection PhpExpressionResultUnusedInspection */
                $method->setAccessible(true);

                return $method->invoke($this);
            } catch (ClientException $e) {
                // Check if this is a rate limit error (429)
                if ($e->getResponse() && 429 === $e->getResponse()->getStatusCode()) {
                    ++$attempt;

                    if ($attempt > $maxRetries) {
                        // Skip test instead of failing CI
                        $this->markTestSkipped(
                            'API rate limit exceeded. Skipping test to prevent CI failure. '.
                            'This is expected behavior when multiple tests run quickly.'
                        );
                    }

                    // Exponential backoff: 5s, 10s (more aggressive)
                    $delay = 5 * $attempt;
                    sleep($delay);
                    continue;
                }

                // Re-throw non-rate-limit exceptions
                throw $e;
            }
        }

        return null; // This should never be reached, but satisfies PHPStan
    }
}
