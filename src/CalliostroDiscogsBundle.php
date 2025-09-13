<?php

declare(strict_types=1);

namespace Calliostro\DiscogsBundle;

use Calliostro\DiscogsBundle\DependencyInjection\CalliostroDiscogsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class CalliostroDiscogsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new CalliostroDiscogsExtension();
        }

        \assert($this->extension instanceof ExtensionInterface);

        return $this->extension;
    }
}
