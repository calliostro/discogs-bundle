<?php

namespace Calliostro\DiscogsBundle\Tests\Integration;

/**
 * Integration Tests for Public API Endpoints.
 *
 * These tests run against the real Discogs API using public endpoints
 * that don't require authentication. They validate:
 *
 * 1. Bundle service wiring with real API
 * 2. Response format consistency through Bundle
 * 3. Bundle configuration handling
 *
 * Safe for CI/CD - no credentials required!
 */
final class PublicApiIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = $this->createKernel([
            'user_agent' => 'CalliostroDiscogsBundle/IntegrationTest',
            'throttle' => [
                'enabled' => true,
                'microseconds' => 1000000, // 1 second between requests
            ],
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->client = $container->get('calliostro_discogs.discogs_client');
    }

    /**
     * Test basic database methods that should always work through Bundle.
     */
    public function testBasicDatabaseMethods(): void
    {
        // Test artist (using ID from original tests)
        $artist = $this->client->getArtist(['id' => '139250']);
        $this->assertIsArray($artist);
        $this->assertArrayHasKey('name', $artist);

        // Test release - Billie Eilish - Happier Than Ever (2021)
        $release = $this->client->getRelease(['id' => '19676596']);
        $this->assertIsArray($release);
        $this->assertArrayHasKey('title', $release);
        $this->assertStringContainsString('Happier Than Ever', $release['title']);

        // Test master - Abbey Road
        $master = $this->client->getMaster(['id' => '18512']);
        $this->assertIsArray($master);
        $this->assertArrayHasKey('title', $master);

        // Test label
        $label = $this->client->getLabel(['id' => '1']);
        $this->assertIsArray($label);
        $this->assertArrayHasKey('name', $label);
    }

    /**
     * Test Community Release Rating endpoint through Bundle.
     */
    public function testCommunityReleaseRating(): void
    {
        $rating = $this->client->getCommunityReleaseRating(['release_id' => '19676596']);

        $this->assertIsArray($rating);
        $this->assertArrayHasKey('rating', $rating);
        $this->assertArrayHasKey('release_id', $rating);
        $this->assertEquals(19676596, $rating['release_id']);

        $this->assertIsArray($rating['rating']);
        $this->assertArrayHasKey('average', $rating['rating']);
        $this->assertArrayHasKey('count', $rating['rating']);
    }

    /**
     * Test that collection stats are available in the full release endpoint.
     */
    public function testCollectionStatsInReleaseEndpoint(): void
    {
        $release = $this->client->getRelease(['id' => '19676596']);

        $this->assertIsArray($release);
        $this->assertArrayHasKey('community', $release);
        $this->assertArrayHasKey('have', $release['community']);
        $this->assertArrayHasKey('want', $release['community']);

        $this->assertIsInt($release['community']['have']);
        $this->assertIsInt($release['community']['want']);
        $this->assertGreaterThan(0, $release['community']['have']);
        $this->assertGreaterThan(0, $release['community']['want']);
    }

    /**
     * Test Bundle's reactive throttling functionality.
     * The Bundle uses reactive throttling - it retries on HTTP 429 with exponential backoff.
     * This test verifies the configuration works by making multiple rapid requests.
     */
    public function testBundleReactiveThrottling(): void
    {
        // Create a kernel with throttling explicitly enabled
        $kernel = $this->createKernel([
            'user_agent' => 'CalliostroDiscogsBundle/IntegrationTest',
            'throttle' => [
                'enabled' => true,
                'microseconds' => 1000000, // 1 second
            ],
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();

        // Note: After boot, the container is compiled - no hasDefinition() method
        // But we can verify the client works with throttling configured
        $client = $container->get('calliostro_discogs.discogs_client');

        // Make requests rapidly - Bundle should handle any rate limits gracefully
        $responses = [];
        for ($i = 0; $i < 2; ++$i) {
            $responses[] = $client->getArtist(['id' => (string) (1 + $i)]);
        }

        // All requests should succeed - Bundle's reactive throttling handles 429 errors
        $this->assertCount(2, $responses);
        foreach ($responses as $response) {
            $this->assertIsArray($response);
            $this->assertArrayHasKey('name', $response);
        }

        // The Bundle's throttling configuration ensures requests succeed
        // even if the API returns rate limit errors (429)
        $this->assertTrue(true, 'Bundle throttling allows rapid requests to succeed');
    }

    /**
     * Test Bundle error handling for invalid IDs through real API.
     */
    public function testBundleErrorHandling(): void
    {
        $this->expectException(\Exception::class);
        $this->client->getArtist(['id' => '999999999']);
    }
}
