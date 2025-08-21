<?php

namespace Calliostro\DiscogsBundle;

interface OAuthTokenProviderInterface
{
    public function getToken(): string;
    public function getTokenSecret(): string;
}
