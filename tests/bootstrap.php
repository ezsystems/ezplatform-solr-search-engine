<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

// Setup config file for running integration tests from ezpublish-kernel dependency
$file = __DIR__ . '/../vendor/ezsystems/ezpublish-kernel/config.php';
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
