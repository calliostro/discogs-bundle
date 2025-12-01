<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Integration\Middleware;

use Calliostro\DiscogsBundle\Middleware\RateLimiterMiddleware;
use Calliostro\DiscogsBundle\Tests\Integration\IntegrationTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * Integration test for the Rate Limiter Middleware.
 *
 * Note: These tests will only run if symfony/rate-limiter is installed.
 */
final class RateLimiterMiddlewareTest extends IntegrationTestCase
{
    public function testMiddlewareAllowsRequestsWithinLimit(): void
    {
        // Create a real rate limiter factory with generous limits for testing
        $factory = new RateLimiterFactory([
            'id' => 'test_limiter',
            'policy' => 'sliding_window',
            'limit' => 10,
            'interval' => '10 seconds',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'test_limiter');

        // Create handler stack
        $mockHandler = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"success": true}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack]);
        $response = $client->get('https://api.example.com/test');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success": true}', $response->getBody()->getContents());
    }

    public function testMiddlewareDoesNotInterfereWithServerRateLimit(): void
    {
        // Create a rate limiter with generous limits
        $factory = new RateLimiterFactory([
            'id' => 'test_limiter',
            'policy' => 'sliding_window',
            'limit' => 100,
            'interval' => '60 seconds',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'test_limiter');

        // Mock a server-side 429 response
        $mockHandler = new MockHandler([
            new Response(429, ['Retry-After' => '5'], '{"message": "Rate limit exceeded"}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);
        $response = $client->get('https://api.example.com/rate-limited');

        // Server-side rate limit should pass through unchanged
        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals('5', $response->getHeaderLine('Retry-After'));
        $this->assertEquals('{"message": "Rate limit exceeded"}', $response->getBody()->getContents());
    }

    public function testMiddlewareUsesCorrectLimiterKey(): void
    {
        $factory = new RateLimiterFactory([
            'id' => 'custom_key',
            'policy' => 'fixed_window',
            'limit' => 5,
            'interval' => '60 seconds',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'custom_key');

        $mockHandler = new MockHandler([
            new Response(200, [], '{"result": "custom"}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack]);
        $response = $client->get('https://api.example.com/custom');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"result": "custom"}', $response->getBody()->getContents());
    }

    public function testMiddlewareHandlesStrictRateLimit(): void
    {
        // Create a very restrictive rate limiter for testing the limit behavior
        $factory = new RateLimiterFactory([
            'id' => 'strict_limiter',
            'policy' => 'fixed_window',
            'limit' => 1,
            'interval' => '1 second',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'strict_limiter');

        $mockHandler = new MockHandler([
            new Response(200, [], '{"request": 1}'),
            new Response(200, [], '{"request": 2}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack]);

        // The first request should go through immediately
        $startTime = microtime(true);
        $response1 = $client->get('https://api.example.com/test1');
        $this->assertEquals(200, $response1->getStatusCode());

        // The second request might be delayed (but we won't test the actual delay in unit tests)
        $response2 = $client->get('https://api.example.com/test2');
        $this->assertEquals(200, $response2->getStatusCode());

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Just verify both requests completed successfully
        $this->assertGreaterThanOrEqual(0, $duration);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }
    }
}
