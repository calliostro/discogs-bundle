<?php

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

        // The parent method can return false, but we guarantee to return an extension
        $extension = $this->extension;
        if (!$extension instanceof ExtensionInterface) {
            throw new \LogicException('Extension must implement ExtensionInterface');
        }

        return $extension;
    }
}
