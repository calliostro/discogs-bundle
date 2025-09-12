<?php

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\DiscogsBundle\ThrottleHandlerStackFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Calliostro\DiscogsBundle\ThrottleHandlerStackFactory
 */
final class ThrottleHandlerStackFactoryTest extends TestCase
{
    public function testFactoryWithoutMicroseconds(): void
    {
        $stack = ThrottleHandlerStackFactory::factory();

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(HandlerStack::class, $stack);

        // Test that it works without retry middleware
        $client = new Client(['handler' => $stack]);
        $mock = new MockHandler([new Response(200, [], 'test')]);
        $stack->setHandler($mock);

        $response = $client->get('http://example.com');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFactoryWithMicroseconds(): void
    {
        $microseconds = 1000000; // 1 second
        $stack = ThrottleHandlerStackFactory::factory($microseconds);

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(HandlerStack::class, $stack);

        // Test successful request (no retry needed)
        $mock = new MockHandler([new Response(200, [], 'success')]);
        $stack->setHandler($mock);
        $client = new Client(['handler' => $stack]);

        $response = $client->get('http://example.com');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRetryOn429Response(): void
    {
        $microseconds = 100000; // 0.1 seconds for fast testing
        $stack = ThrottleHandlerStackFactory::factory($microseconds);

        // Mock: First request returns 429, second returns 200
        $mock = new MockHandler([
            new Response(429, [], 'Rate Limited'),
            new Response(200, [], 'Success after retry'),
        ]);
        $stack->setHandler($mock);
        $client = new Client(['handler' => $stack, 'http_errors' => false]);

        $response = $client->get('http://example.com');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success after retry', (string) $response->getBody());
    }

    public function testMaxRetriesExceeded(): void
    {
        $microseconds = 100000; // 0.1 seconds for fast testing
        $stack = ThrottleHandlerStackFactory::factory($microseconds);

        // Mock: All requests return 429 (exceeds max retries)
        $mock = new MockHandler([
            new Response(429, [], 'Rate Limited'),
            new Response(429, [], 'Rate Limited'),
            new Response(429, [], 'Rate Limited'),
            new Response(429, [], 'Rate Limited'), // The final attempt also fails
        ]);
        $stack->setHandler($mock);
        $client = new Client(['handler' => $stack, 'http_errors' => false]);

        $response = $client->get('http://example.com');
        $this->assertEquals(429, $response->getStatusCode());
    }

    public function testNoRetryOnOtherErrors(): void
    {
        $microseconds = 100000;
        $stack = ThrottleHandlerStackFactory::factory($microseconds);

        // Mock: 500 error should not be retried
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error'),
        ]);
        $stack->setHandler($mock);
        $client = new Client(['handler' => $stack, 'http_errors' => false]);

        $response = $client->get('http://example.com');
        $this->assertEquals(500, $response->getStatusCode());
    }
}
