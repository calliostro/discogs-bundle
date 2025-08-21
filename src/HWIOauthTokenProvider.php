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
        
        if ($token !== null && method_exists($token, 'getRawToken')) {
            /** @var mixed $token */
            $rawToken = $token->getRawToken();
            return $rawToken[$name] ?? '';
        }

        return '';
    }
}
