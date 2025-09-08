<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ])
    ->setRiskyAllowed(true)
;
