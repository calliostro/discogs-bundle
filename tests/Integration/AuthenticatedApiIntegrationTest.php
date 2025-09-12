<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Integration;

/**
 * Integration Tests for All Authentication Levels through Bundle.
 *
 * These tests validate all authentication levels against the real Discogs API
 * through the Symfony Bundle service configuration:
 * 1. No authentication (public data)
 * 2. Consumer credentials (search)
 * 3. Personal access token (your data)
 * 4. OAuth tokens (full account access)
 *
 * Requires environment variables:
 * - DISCOGS_CONSUMER_KEY
 * - DISCOGS_CONSUMER_SECRET
 * - DISCOGS_PERSONAL_ACCESS_TOKEN
 * - DISCOGS_OAUTH_TOKEN (optional)
 * - DISCOGS_OAUTH_TOKEN_SECRET (optional)
 */
final class AuthenticatedApiIntegrationTest extends IntegrationTestCase
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $personalToken;
    private string $oauthToken;
    private string $oauthTokenSecret;

    protected function setUp(): void
    {
        $this->consumerKey = getenv('DISCOGS_CONSUMER_KEY') ?: '';
        $this->consumerSecret = getenv('DISCOGS_CONSUMER_SECRET') ?: '';
        $this->personalToken = getenv('DISCOGS_PERSONAL_ACCESS_TOKEN') ?: '';
        $this->oauthToken = getenv('DISCOGS_OAUTH_TOKEN') ?: '';
        $this->oauthTokenSecret = getenv('DISCOGS_OAUTH_TOKEN_SECRET') ?: '';

        if (empty($this->consumerKey) || empty($this->consumerSecret) || empty($this->personalToken)) {
            $this->markTestSkipped('Authentication credentials not available');
        }

        parent::setUp();
    }

    /**
     * Helper to skip test if credentials are invalid.
     */
    private function skipIfInvalidCredentials(\Exception $e): void
    {
        if ($e instanceof \GuzzleHttp\Exception\ClientException
            && $e->getResponse()
            && 401 === $e->getResponse()->getStatusCode()) {
            $this->markTestSkipped('Invalid credentials provided - test requires valid Discogs API credentials');
        }
        throw $e;
    }

    /**
     * Level 2: Consumer Credentials - Search enabled through Bundle.
     */
    public function testLevel2ConsumerCredentials(): void
    {
        $kernel = $this->createKernel([
            'user_agent' => 'CalliostroDiscogsBundle/AuthTest',
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();
        $client = $container->get('calliostro_discogs.discogs_client');

        // All public endpoints should still work
        $artist = $client->getArtist(['id' => '1']);
        $this->assertIsArray($artist);
        $this->assertArrayHasKey('name', $artist);

        // Search should now work with consumer credentials
        $searchResults = $client->search(['q' => 'Daft Punk', 'type' => 'artist']);
        $this->assertIsArray($searchResults);
        $this->assertArrayHasKey('results', $searchResults);
        $this->assertGreaterThan(0, \count($searchResults['results']));
    }

    /**
     * Level 3: Personal Access Token - Your account access through Bundle.
     */
    public function testLevel3PersonalAccessToken(): void
    {
        $kernel = $this->createKernel([
            'user_agent' => 'CalliostroDiscogsBundle/PersonalTest',
            'personal_access_token' => $this->personalToken,
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();
        $client = $container->get('calliostro_discogs.discogs_client');

        // All previous functionality should work
        $artist = $client->getArtist(['id' => '1']);
        $this->assertIsArray($artist);

        $searchResults = $client->search(['q' => 'Jazz', 'type' => 'release']);
        $this->assertIsArray($searchResults);
        $this->assertArrayHasKey('results', $searchResults);

        // Test that we can successfully make authenticated requests
        $this->assertIsArray($searchResults);
        $this->assertNotEmpty($searchResults['results']);
    }

    /**
     * Test rate limiting behavior with authenticated requests through Bundle.
     */
    public function testRateLimitingWithAuthentication(): void
    {
        $kernel = $this->createKernel([
            'personal_access_token' => $this->personalToken,
            'throttle' => [
                'enabled' => true,
                'microseconds' => 500000, // 0.5 seconds for authenticated requests
            ],
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();
        $client = $container->get('calliostro_discogs.discogs_client');

        // Make several requests in quick succession
        // Authenticated requests have higher rate limits
        $startTime = microtime(true);

        for ($i = 0; $i < 3; ++$i) {
            $artist = $client->getArtist(['id' => (string) (1 + $i)]);
            $this->assertIsArray($artist);
            $this->assertArrayHasKey('name', $artist);
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // With authentication, this should complete quickly (< 3 seconds)
        $this->assertLessThan(3.0, $duration, 'Authenticated requests took too long - possible rate limiting issue');
    }

    /**
     * Level 4: OAuth Tokens - Full account access (bypassing Bundle config).
     *
     * Note: OAuth is not configured in Bundle config but can be used directly
     * via the underlying library factory methods for advanced use cases.
     */
    public function testLevel4OAuthDirectLibraryUsage(): void
    {
        if (empty($this->oauthToken) || empty($this->oauthTokenSecret)) {
            $this->markTestSkipped('OAuth tokens not available - requires DISCOGS_OAUTH_TOKEN and DISCOGS_OAUTH_TOKEN_SECRET');
        }

        // Create the OAuth client directly via the library (not Bundle config)
        $client = \Calliostro\Discogs\ClientFactory::createWithOAuth(
            $this->consumerKey,
            $this->consumerSecret,
            $this->oauthToken,
            $this->oauthTokenSecret
        );

        // Test identity endpoint (OAuth-specific functionality)
        try {
            $identity = $client->getIdentity();
            $this->assertIsArray($identity);
            $this->assertArrayHasKey('username', $identity);
            $this->assertNotEmpty($identity['username']);
        } catch (\Exception $e) {
            $this->skipIfInvalidCredentials($e);
        }

        // Test search with OAuth (should work like other auth methods)
        try {
            $searchResults = $client->search(['q' => 'Electronic', 'type' => 'artist', 'per_page' => 5]);
            $this->assertIsArray($searchResults);
            $this->assertArrayHasKey('results', $searchResults);
            $this->assertGreaterThan(0, \count($searchResults['results']));
        } catch (\Exception $e) {
            $this->skipIfInvalidCredentials($e);
        }
    }

    /**
     * Test error handling with different authentication levels through Bundle.
     */
    public function testErrorHandlingAcrossAuthLevels(): void
    {
        // Test with consumer credentials
        $kernel = $this->createKernel([
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
            'throttle' => [
                'enabled' => true,
                'microseconds' => 1000000, // 1 second for consumer credential requests
            ],
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();
        $client = $container->get('calliostro_discogs.discogs_client');

        try {
            $client->getArtist(['id' => '999999999']); // Non-existent artist
            $this->fail('Should have thrown exception for non-existent artist');
        } catch (\Exception $e) {
            $this->assertStringContainsStringIgnoringCase('not found', $e->getMessage());
        }
    }
}
