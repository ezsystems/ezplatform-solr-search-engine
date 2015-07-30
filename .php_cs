<?php

return Symfony\CS\Config\Config::create()
    ->setUsingLinter(false)
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        'concat_with_spaces',
        '-concat_without_spaces',
        '-empty_return',
        '-phpdoc_params',
        '-phpdoc_separation',
        '-phpdoc_to_comment',
        '-spaces_cast',
        '-blankline_after_open_tag',
        '-single_blank_line_before_namespace',
        // psr0 has weird issues with our PSR-4 layout, so deactivating it.
        '-psr0',
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in(__DIR__)
            ->notPath('phpunit.xml')
            ->exclude([
                'vendor',
            ])
            ->files()->name('*.php')
    )
;
