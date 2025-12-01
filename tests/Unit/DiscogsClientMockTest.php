<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\Tests\Unit;

use Calliostro\Discogs\DiscogsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

final class DiscogsClientMockTest extends UnitTestCase
{
    private MockHandler $mockHandler;
    private HandlerStack $handlerStack;
    private Client $httpClient;
    private DiscogsClient $client;

    public function testGetArtistSuccess(): void
    {
        $expectedResponse = [
            'id' => 4470662,
            'name' => 'Billie Eilish',
            'profile' => 'American singer-songwriter born in 2001',
            'images' => [],
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse) ?: '{}')
        );

        $result = $this->client->getArtist(artistId: 1);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetArtistNotFound(): void
    {
        $this->mockHandler->append(
            new Response(404, ['Content-Type' => 'application/json'], '{"message": "Artist not found."}')
        );

        $this->expectException(\GuzzleHttp\Exception\ClientException::class);

        $this->client->getArtist(artistId: 999999999);
    }

    public function testSearchWithResults(): void
    {
        $expectedResponse = [
            'results' => [
                [
                    'id' => 30359313,
                    'title' => 'Billie Eilish - Happier Than Ever',
                    'type' => 'release',
                ],
            ],
            'pagination' => [
                'page' => 1,
                'per_page' => 50,
                'items' => 1,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse) ?: '{}')
        );

        $result = $this->client->search(q: 'Billie Eilish', type: 'release');

        $this->assertEquals($expectedResponse, $result);
        $this->assertCount(1, $result['results']);
    }

    public function testSearchWithNoResults(): void
    {
        $expectedResponse = [
            'results' => [],
            'pagination' => [
                'page' => 1,
                'per_page' => 50,
                'items' => 0,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse) ?: '{}')
        );

        $result = $this->client->search(q: 'NonexistentModernArtist2024');

        $this->assertEquals($expectedResponse, $result);
        $this->assertEmpty($result['results']);
    }

    public function testRateLimitHandling(): void
    {
        // Mock a rate limit response
        $this->mockHandler->append(
            new Response(429, ['Retry-After' => '1'], '{"message": "You are making requests too quickly."}')
        );

        // Without rate limiter middleware, the client gets the 429 response directly
        $client = new Client(['handler' => $this->handlerStack, 'http_errors' => false]);
        $apiClient = new DiscogsClient($client);

        // This test verifies that rate limit responses are handled properly
        $result = $apiClient->getArtist(artistId: '1');

        // The client should return the 429 rate limit response
        $this->assertEquals(['message' => 'You are making requests too quickly.'], $result);
    }

    public function testMultipleRequestsWithMocking(): void
    {
        $responses = [
            ['id' => 4470662, 'name' => 'Billie Eilish'],
            ['id' => 1039492, 'name' => 'Taylor Swift'],
            ['id' => 2727177, 'name' => 'The Weeknd'],
        ];

        foreach ($responses as $response) {
            $this->mockHandler->append(
                new Response(200, ['Content-Type' => 'application/json'], json_encode($response) ?: '{}')
            );
        }

        $results = [];
        for ($i = 1; $i <= 3; ++$i) {
            $results[] = $this->client->getArtist(artistId: $i);
        }

        $this->assertCount(3, $results);
        $this->assertEquals('Billie Eilish', $results[0]['name']);
        $this->assertEquals('Taylor Swift', $results[1]['name']);
        $this->assertEquals('The Weeknd', $results[2]['name']);
    }

    public function testInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], 'invalid json{')
        );

        // The actual exception thrown by the Discogs client is RuntimeException, not JsonException
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->client->getArtist(artistId: 1);
    }

    public function testServerError(): void
    {
        $this->mockHandler->append(
            new Response(500, ['Content-Type' => 'application/json'], '{"message": "Internal Server Error"}')
        );

        $this->expectException(\GuzzleHttp\Exception\ServerException::class);

        $this->client->getArtist(artistId: 1);
    }

    public function testClientWithCustomHeaders(): void
    {
        $customHeaders = [
            'User-Agent' => 'MyTestApp/1.0',
            'Authorization' => 'Discogs token=test_token',
        ];

        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 1, "name": "Test"}')
        );

        $customClient = new Client([
            'handler' => $this->handlerStack,
            'headers' => $customHeaders,
        ]);

        $apiClient = new DiscogsClient($customClient);
        $result = $apiClient->getArtist(artistId: 1);

        $this->assertEquals(['id' => 1, 'name' => 'Test'], $result);

        // Verify the request was made (check last request)
        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertNotNull($lastRequest);
    }

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->handlerStack = HandlerStack::create($this->mockHandler);
        $this->httpClient = new Client(['handler' => $this->handlerStack]);

        // Use reflection to create a DiscogsClient with our mocked HTTP client
        $this->client = new DiscogsClient($this->httpClient);
    }
}
