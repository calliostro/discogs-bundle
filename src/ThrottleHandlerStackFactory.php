<?php

namespace Calliostro\DiscogsBundle;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Legacy throttling implementation using Guzzle retry middleware.
 *
 * Works by retrying requests on HTTP 429 (rate limit) with exponential backoff.
 * For more advanced rate limiting, consider implementing the application layer.
 */
final class ThrottleHandlerStackFactory
{
    /**
     * Create a handler stack with retry middleware for rate limiting.
     */
    public static function factory(?int $microseconds = null): HandlerStack
    {
        $handler = HandlerStack::create();

        if (null !== $microseconds) {
            // Simple retry-based rate limiting
            // Retries up to 3 times on HTTP 429 with exponential backoff
            $handler->push(
                Middleware::retry(
                    function (int $retries, RequestInterface $request, ?ResponseInterface $response = null): bool {
                        // Retry on rate limit (429) up to 3 times
                        return $retries < 3 && $response && 429 === $response->getStatusCode();
                    },
                    function (int $retries) use ($microseconds): int {
                        // Exponential backoff delay
                        return (int) (($microseconds / 1000000) * 2 ** $retries * 1000);
                    }
                ),
                'throttle'
            );
        }

        return $handler;
    }
}
