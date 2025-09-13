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
    /**
     * Test basic database methods that should always work through Bundle.
     */
    public function testBasicDatabaseMethods(): void
    {
        // Test artist (using ID from original tests)
        $artist = $this->client->getArtist(artistId: 139250);
        $this->assertArrayHasKey('name', $artist);

        // Test release - Billie Eilish - Happier Than Ever (2021)
        $release = $this->client->getRelease(releaseId: 19676596);
        $this->assertArrayHasKey('title', $release);
        $this->assertStringContainsString('Happier Than Ever', $release['title']);

        // Test master - Billie Eilish - Happier Than Ever (2021)
        $master = $this->client->getMaster(masterId: 2234794);
        $this->assertArrayHasKey('title', $master);

        // Test label
        $label = $this->client->getLabel(labelId: 1);
        $this->assertArrayHasKey('name', $label);
    }

    /**
     * Test Community Release Rating endpoint through Bundle.
     */
    public function testCommunityReleaseRating(): void
    {
        $rating = $this->client->getCommunityReleaseRating(releaseId: 19676596);

        $this->assertArrayHasKey('rating', $rating);
        $this->assertArrayHasKey('release_id', $rating);
        $this->assertEquals(19676596, $rating['release_id']);

        $this->assertArrayHasKey('average', $rating['rating']);
        $this->assertArrayHasKey('count', $rating['rating']);
    }

    /**
     * Test that collection stats are available in the full release endpoint.
     */
    public function testCollectionStatsInReleaseEndpoint(): void
    {
        $release = $this->client->getRelease(releaseId: 19676596);

        $this->assertArrayHasKey('community', $release);
        $this->assertArrayHasKey('have', $release['community']);
        $this->assertArrayHasKey('want', $release['community']);

        $this->assertIsInt($release['community']['have']);
        $this->assertIsInt($release['community']['want']);
        $this->assertGreaterThan(0, $release['community']['have']);
        $this->assertGreaterThan(0, $release['community']['want']);
    }

    /**
     * Test Bundle's basic functionality without rate limiting.
     * The Bundle is ultra-lightweight and doesn't include built-in throttling.
     * Rate limiting can be added via Symfony's rate-limiter component as needed.
     */
    public function testBundleBasicFunctionality(): void
    {
        // Create a kernel with minimal configuration
        $kernel = $this->createKernel([
            'user_agent' => 'CalliostroDiscogsBundle/IntegrationTest',
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();

        // Verify the client works without any rate limiting
        $client = $container->get('calliostro_discogs.discogs_client');
        \assert($client instanceof \Calliostro\Discogs\DiscogsClient);

        // Make requests - Bundle is ultra-lightweight with no built-in throttling
        $responses = [];
        for ($i = 0; $i < 2; ++$i) {
            $responses[] = $client->getArtist(artistId: 1 + $i);
        }

        // All requests should succeed
        $this->assertCount(2, $responses);
        foreach ($responses as $response) {
            $this->assertArrayHasKey('name', $response);
        }

        // Bundle is ultra-lightweight - no built-in throttling overhead
        $this->addToAssertionCount(1); // Ultra-lightweight bundle allows rapid requests
    }

    /**
     * Test Bundle error handling for invalid IDs through real API.
     */
    public function testBundleErrorHandling(): void
    {
        $this->expectException(\Exception::class);
        $this->client->getArtist(artistId: 999999999);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = $this->createKernel([
            'user_agent' => 'CalliostroDiscogsBundle/IntegrationTest',
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();

        $client = $container->get('calliostro_discogs.discogs_client');
        \assert($client instanceof \Calliostro\Discogs\DiscogsClient);
        $this->client = $client;
    }
}
