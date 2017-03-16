<?php

if (class_exists('Symfony\CS\Config\Config')) {
    // PHP-CS-Fixer 1.x syntax (deprecated)
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
            '-phpdoc_annotation_without_dot',
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
}

// PHP-CS-Fixer 2.x syntax
return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'concat_space' => ['spacing' => 'one'],
        'array_syntax' => false,
        'simplified_null_return' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'phpdoc_to_comment' => false,
        'cast_spaces' => false,
        'blank_line_after_opening_tag' => false,
        'single_blank_line_before_namespace' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_no_alias_tag' => false,
        'space_after_semicolon' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude([
                'vendor',
            ])
            ->files()->name('*.php')
    )
;
