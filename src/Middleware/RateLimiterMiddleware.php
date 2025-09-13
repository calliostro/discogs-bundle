<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Guzzle middleware that integrates with Symfony Rate Limiter component.
 *
 * This middleware provides advanced rate limiting capabilities by leveraging
 * the Symfony Rate Limiter component, allowing users to configure their own
 * rate limiting strategies (fixed window, sliding window, token bucket, etc.).
 */
final class RateLimiterMiddleware
{
    public function __construct(
        private readonly RateLimiterFactory $rateLimiterFactory,
        private readonly string $limiterKey = 'discogs_api',
    ) {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            $rateLimiter = $this->rateLimiterFactory->create($this->limiterKey);

            // Try to consume from rate limiter
            $limit = $rateLimiter->consume(1);

            if (!$limit->isAccepted()) {
                // If rate limit exceeded, wait for the retry after time
                $retryAfter = $limit->getRetryAfter();
                $waitTime = $retryAfter->getTimestamp() - time();
                if ($waitTime > 0) {
                    usleep($waitTime * 1000000); // Convert to microseconds
                }

                // Try again after waiting
                $limit = $rateLimiter->consume(1);
            }

            return $handler($request, $options);
        };
    }
}
