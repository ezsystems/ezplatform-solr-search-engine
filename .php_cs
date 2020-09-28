<?php

return EzSystems\EzPlatformCodeStyle\PhpCsFixer\Config::create()->setFinder(
    PhpCsFixer\Finder::create()
        ->in(__DIR__ . '/lib')
        ->in(__DIR__ . '/bundle')
        ->in(__DIR__ . '/tests')
        ->files()->name('*.php')
);