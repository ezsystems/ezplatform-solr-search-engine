<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

// Setup config file for running integration tests from ezplatform-kernel dependency
$file = __DIR__ . '/../vendor/ezsystems/ezplatform-kernel/config.php';
if (!file_exists($file)) {
    if (!symlink($file . '-DEVELOPMENT', $file)) {
        throw new RuntimeException('Could not symlink config.php-DEVELOPMENT to config.php');
    }
}

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies using composer to run the test suite.');
}

$autoload = require_once $file;

// Polyfill for PHPUnit 6.0 and up
if (!class_exists('PHPUnit_Framework_Constraint', true)) {
    class_alias('PHPUnit\Framework\Constraint\Constraint', 'PHPUnit_Framework_Constraint');
}
