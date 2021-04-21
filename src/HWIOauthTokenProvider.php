<?php

namespace Calliostro\DiscogsBundle;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class HWIOauthTokenProvider implements OAuthTokenProviderInterface
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getToken(): string
    {
        return $this->getRawToken('oauth_token');
    }

    public function getTokenSecret(): string
    {
        return $this->getRawToken('oauth_token_secret');
    }

    private function getRawToken($name): string
    {
        // getToken() must be a HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\AbstractOAuthToken
        if (
            !is_null($this->tokenStorage->getToken()) &&
            method_exists($this->tokenStorage->getToken(), 'getRawToken')
        ) {
            /** @noinspection PhpUndefinedMethodInspection */
            $token = $this->tokenStorage->getToken()->getRawToken();

            return $token[$name];
        }

        return '';
    }
}
