<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle\DependencyInjection;

use Calliostro\Discogs\DiscogsClient;
use Calliostro\Discogs\DiscogsClientFactory as BaseDiscogsClientFactory;

/**
 * Factory service for creating DiscogsClient instances with runtime validation.
 * This handles credential validation at service creation time rather than
 * container compilation time, allowing environment variables to be resolved.
 */
final class DiscogsClientFactory
{
    /**
     * Create a DiscogsClient with runtime credential validation.
     *
     * @param array<string, mixed> $options
     */
    public function createClient(
        ?string $personalAccessToken,
        ?string $consumerKey,
        ?string $consumerSecret,
        array $options = [],
    ): DiscogsClient {
        // Trim all credentials to handle whitespace-only values and null
        $personalAccessToken = $personalAccessToken ? trim($personalAccessToken) : '';
        $consumerKey = $consumerKey ? trim($consumerKey) : '';
        $consumerSecret = $consumerSecret ? trim($consumerSecret) : '';

        // Validate and create client based on available credentials
        if (!empty($personalAccessToken)) {
            return $this->createWithPersonalAccessToken($personalAccessToken, $options);
        }

        if (!empty($consumerKey) && !empty($consumerSecret)) {
            return $this->createWithConsumerCredentials($consumerKey, $consumerSecret, $options);
        }

        // Check for partial OAuth credentials and provide helpful error
        if (!empty($consumerKey) || !empty($consumerSecret)) {
            throw new \InvalidArgumentException('Incomplete OAuth credentials provided. Both consumer_key and consumer_secret are required for OAuth authentication. '.$this->getSetupInstructions());
        }

        // Create anonymous client (rate-limited) - this is allowed
        return BaseDiscogsClientFactory::create($options);
    }

    /**
     * Create client with Personal Access Token and validate it.
     *
     * @param array<string, mixed> $options
     */
    private function createWithPersonalAccessToken(string $token, array $options): DiscogsClient
    {
        if (\strlen($token) < 10) {
            throw new \InvalidArgumentException(\sprintf('Personal access token must be at least 10 characters long, got %d characters. %s', \strlen($token), $this->getSetupInstructions()));
        }

        return BaseDiscogsClientFactory::createWithPersonalAccessToken($token, $options);
    }

    /**
     * Create client with Consumer Credentials.
     *
     * @param array<string, mixed> $options
     */
    private function createWithConsumerCredentials(string $consumerKey, string $consumerSecret, array $options): DiscogsClient
    {
        return BaseDiscogsClientFactory::createWithConsumerCredentials($consumerKey, $consumerSecret, $options);
    }

    /**
     * Get helpful setup instructions for the user.
     */
    private function getSetupInstructions(): string
    {
        return "\n\nTo configure Discogs API credentials:\n".
               "1. Personal Access Token (recommended):\n".
               "   - Get your token from: https://www.discogs.com/settings/developers\n".
               "   - Add to your .env.local: DISCOGS_PERSONAL_ACCESS_TOKEN=your_token_here\n".
               "   - Configure in config/packages/calliostro_discogs.yaml:\n".
               "     calliostro_discogs:\n".
               "       personal_access_token: '%env(DISCOGS_PERSONAL_ACCESS_TOKEN)%'\n\n".
               "2. OAuth Consumer Credentials (for applications):\n".
               "   - Add to your .env.local:\n".
               "     DISCOGS_CONSUMER_KEY=your_key_here\n".
               "     DISCOGS_CONSUMER_SECRET=your_secret_here\n".
               "   - Configure in config/packages/calliostro_discogs.yaml:\n".
               "     calliostro_discogs:\n".
               "       consumer_key: '%env(DISCOGS_CONSUMER_KEY)%'\n".
               "       consumer_secret: '%env(DISCOGS_CONSUMER_SECRET)%'\n\n".
               "3. Anonymous access (limited rate limits):\n".
               "   - No configuration needed, but subject to strict rate limits\n";
    }
}
