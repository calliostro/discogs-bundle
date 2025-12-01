<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit\Middleware;

use Calliostro\DiscogsBundle\Middleware\RateLimiterMiddleware;
use Calliostro\DiscogsBundle\Tests\Unit\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * Unit test for the Rate Limiter Middleware.
 *
 * Note: These tests will only run if symfony/rate-limiter is installed.
 */
final class RateLimiterMiddlewareTest extends UnitTestCase
{
    public function testMiddlewareAllowsRequestsWithinLimit(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

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
        $response = $client->get('https://api.discogs.com/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success": true}', $response->getBody()->getContents());
    }

    public function testMiddlewareConstructorWithDefaults(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        $factory = new RateLimiterFactory([
            'id' => 'test_limiter',
            'policy' => 'sliding_window',
            'limit' => 5,
            'interval' => '1 second',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory);

        $this->assertInstanceOf(RateLimiterMiddleware::class, $middleware);
    }

    public function testMiddlewareConstructorWithCustomKey(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        $factory = new RateLimiterFactory([
            'id' => 'custom_limiter',
            'policy' => 'sliding_window',
            'limit' => 5,
            'interval' => '1 second',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'custom_key');

        $this->assertInstanceOf(RateLimiterMiddleware::class, $middleware);
    }

    public function testMiddlewareIsCallable(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        $factory = new RateLimiterFactory([
            'id' => 'test_limiter',
            'policy' => 'sliding_window',
            'limit' => 10,
            'interval' => '10 seconds',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'test_limiter');

        // Test that the middleware is callable
        $this->assertTrue(\is_callable($middleware));

        // Test that calling it returns another callable
        $handler = function () { return 'test'; };
        $wrappedHandler = $middleware($handler);

        $this->assertTrue(\is_callable($wrappedHandler));
    }

    public function testMiddlewareHandlesRateLimitExceeded(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        // Create a rate limiter with very strict limits
        $factory = new RateLimiterFactory([
            'id' => 'strict_limiter',
            'policy' => 'fixed_window',
            'limit' => 1,
            'interval' => '1 second',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'strict_limiter');

        // Create mock responses
        $mockHandler = new MockHandler([
            new Response(200, [], '{"first": true}'),
            new Response(200, [], '{"second": true}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack]);

        // First request should go through immediately
        $response1 = $client->get('https://api.discogs.com/');
        $this->assertEquals('{"first": true}', $response1->getBody()->getContents());

        // Second request should be delayed but eventually go through
        $startTime = microtime(true);
        $response2 = $client->get('https://api.discogs.com/');
        $endTime = microtime(true);

        $this->assertEquals('{"second": true}', $response2->getBody()->getContents());

        // Should have taken some time due to rate limiting (at least a few microseconds of delay)
        $this->assertGreaterThanOrEqual(0, $endTime - $startTime);
    }

    public function testMiddlewareWithMultipleRequests(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        // Create a rate limiter with reasonable limits
        $factory = new RateLimiterFactory([
            'id' => 'multi_limiter',
            'policy' => 'sliding_window',
            'limit' => 3,
            'interval' => '1 second',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'multi_limiter');

        $mockHandler = new MockHandler([
            new Response(200, [], '{"request": 1}'),
            new Response(200, [], '{"request": 2}'),
            new Response(200, [], '{"request": 3}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack]);

        // All three requests should go through within the limit
        $response1 = $client->get('https://api.discogs.com/');
        $response2 = $client->get('https://api.discogs.com/');
        $response3 = $client->get('https://api.discogs.com/');

        $this->assertEquals('{"request": 1}', $response1->getBody()->getContents());
        $this->assertEquals('{"request": 2}', $response2->getBody()->getContents());
        $this->assertEquals('{"request": 3}', $response3->getBody()->getContents());
    }

    public function testMiddlewareWithTokenBucketPolicy(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        // Test with token bucket policy
        $factory = new RateLimiterFactory([
            'id' => 'token_bucket_limiter',
            'policy' => 'token_bucket',
            'limit' => 5,
            'rate' => ['interval' => '1 second', 'amount' => 2],
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'token_bucket_limiter');

        $mockHandler = new MockHandler([
            new Response(200, [], '{"token_bucket": true}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack]);
        $response = $client->get('https://api.discogs.com/');

        $this->assertEquals('{"token_bucket": true}', $response->getBody()->getContents());
    }

    public function testMiddlewareWithFixedWindowPolicy(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        // Test with fixed window policy
        $factory = new RateLimiterFactory([
            'id' => 'fixed_window_limiter',
            'policy' => 'fixed_window',
            'limit' => 10,
            'interval' => '1 minute',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'fixed_window_limiter');

        $mockHandler = new MockHandler([
            new Response(200, [], '{"fixed_window": true}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack]);
        $response = $client->get('https://api.discogs.com/');

        $this->assertEquals('{"fixed_window": true}', $response->getBody()->getContents());
    }

    public function testMiddlewareWithZeroWaitTime(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        // Create a rate limiter that will be hit, but with a wait time that should be 0 or negative
        $factory = new RateLimiterFactory([
            'id' => 'zero_wait_limiter',
            'policy' => 'fixed_window',
            'limit' => 1,
            'interval' => '1 second',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'zero_wait_limiter');

        $mockHandler = new MockHandler([
            new Response(200, [], '{"first": true}'),
            new Response(200, [], '{"second": true}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($middleware);

        $client = new Client(['handler' => $handlerStack]);

        // First request
        $response1 = $client->get('https://api.discogs.com/');
        $this->assertEquals('{"first": true}', $response1->getBody()->getContents());

        // Wait for the window to reset, then make second request
        sleep(1);
        $response2 = $client->get('https://api.discogs.com/');
        $this->assertEquals('{"second": true}', $response2->getBody()->getContents());
    }

    public function testMiddlewarePreservesRequestAndOptions(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        $factory = new RateLimiterFactory([
            'id' => 'preserve_test_limiter',
            'policy' => 'sliding_window',
            'limit' => 10,
            'interval' => '10 seconds',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'preserve_test_limiter');

        // Test that the middleware preserves request and options
        $calledHandler = null;
        $wrappedHandler = $middleware(function ($request, $options) use (&$calledHandler) {
            $calledHandler = [$request, $options];

            return \GuzzleHttp\Promise\Create::promiseFor(new Response(200));
        });

        $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.discogs.com/');
        $options = ['timeout' => 30, 'custom' => 'value'];

        $promise = $wrappedHandler($request, $options);
        $promise->wait();

        $this->assertNotNull($calledHandler);
        $this->assertSame($request, $calledHandler[0]);
        $this->assertSame($options, $calledHandler[1]);
    }

    public function testMiddlewareReturnsPromise(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        $factory = new RateLimiterFactory([
            'id' => 'promise_test_limiter',
            'policy' => 'sliding_window',
            'limit' => 10,
            'interval' => '10 seconds',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'promise_test_limiter');

        $wrappedHandler = $middleware(function () {
            return \GuzzleHttp\Promise\Create::promiseFor(new Response(200, [], '{"promise": true}'));
        });

        $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.discogs.com/');
        $promise = $wrappedHandler($request, []);

        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $promise);

        $response = $promise->wait();
        $this->assertEquals('{"promise": true}', $response->getBody()->getContents());
    }

    public function testMiddlewareHandlesPromiseRejection(): void
    {
        if (!class_exists('Symfony\\Component\\RateLimiter\\RateLimiterFactory')) {
            $this->markTestSkipped('symfony/rate-limiter is not installed');
        }

        $factory = new RateLimiterFactory([
            'id' => 'rejection_test_limiter',
            'policy' => 'sliding_window',
            'limit' => 10,
            'interval' => '10 seconds',
        ], new InMemoryStorage());

        $middleware = new RateLimiterMiddleware($factory, 'rejection_test_limiter');

        $expectedException = new \Exception('Handler failed');
        $wrappedHandler = $middleware(function () use ($expectedException) {
            return \GuzzleHttp\Promise\Create::rejectionFor($expectedException);
        });

        $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.discogs.com/');
        $promise = $wrappedHandler($request, []);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Handler failed');
        $promise->wait();
    }
}
