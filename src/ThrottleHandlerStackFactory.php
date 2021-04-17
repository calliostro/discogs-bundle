<?php

namespace Calliostro\DiscogsBundle;

use Discogs\Subscriber\ThrottleSubscriber;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class ThrottleHandlerStackFactory
{
    public static function factory(?ThrottleSubscriber $subscriber): HandlerStack
    {
        $handler = HandlerStack::create();

        if ($subscriber) {
            $handler->push(Middleware::retry($subscriber->decider(), $subscriber->delay()), 'throttle');
        }

        return $handler;
    }
}
