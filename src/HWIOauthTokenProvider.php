<?php

namespace Calliostro\DiscogsBundle;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class HWIOauthTokenProvider implements OAuthTokenProviderInterface
{
    public function __construct(public readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function getToken(): string
    {
        return $this->getRawToken('oauth_token');
    }

    public function getTokenSecret(): string
    {
        return $this->getRawToken('oauth_token_secret');
    }

    private function getRawToken(string $name): string
    {
        // getToken() must be a HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\AbstractOAuthToken
        $token = $this->tokenStorage->getToken();

        if (null !== $token && method_exists($token, 'getRawToken')) {
            // Safe call using method_exists check - HWIOAuthBundle's AbstractOAuthToken has this method
            $rawToken = \call_user_func([$token, 'getRawToken']);
            if (\is_array($rawToken) && isset($rawToken[$name])) {
                return (string) $rawToken[$name];
            }
        }

        return '';
    }
}
