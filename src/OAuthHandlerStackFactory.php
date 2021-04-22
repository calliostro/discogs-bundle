<?php

namespace Calliostro\DiscogsBundle;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

final class OAuthHandlerStackFactory
{
    public static function factory(?Oauth1 $oauth): HandlerStack
    {
        $handler = HandlerStack::create();

        if ($oauth) {
            $handler->push($oauth, 'oauth');
        }

        return $handler;
    }
}
