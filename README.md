# Solr Search Engine Bundle for eZ Platform

[![Build Status](https://img.shields.io/travis/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://travis-ci.org/ezsystems/ezplatform-solr-search-engine)
[![Downloads](https://img.shields.io/packagist/dt/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://packagist.org/packages/ezsystems/ezplatform-solr-search-engine)
[![Latest release](https://img.shields.io/github/release/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://github.com/ezsystems/ezplatform-solr-search-engine/releases)
[![License](https://img.shields.io/github/license/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](LICENSE)

Solr Search Engine Bundle for use with Solr, for requirments see [doc.ibexa.co](https://doc.ibexa.co/en/latest/getting_started/requirements/) _(remember to pick the sofware version you are on)_.

#####  Overview of features

See https://doc.ibexa.co/en/latest/guide/search/search/#feature-comparison

## Install

- Ibexa DXP / eZ Platform: https://doc.ibexa.co/en/latest/guide/search/solr/#how-to-set-up-solr-search-engine
- eZ Publish Platform 5.4.x: https://doc.ez.no/display/EZP/Solr+Search+Engine+Bundle



### Testing locally

For Contributing to this Bundle, you should make sure to run both unit and integration tests *(from ezplatform-kernel repo)*.

1. Setup this repository locally

    ```bash
    git clone git@github.com:ezsystems/ezplatform-solr-search-engine.git solr
    cd solr
    composer install
    ```

    At this point you should be able to run unit tests:
    ```bash
    php vendor/bin/phpunit --bootstrap tests/bootstrap.php
    ```

2. Install and configure Solr

    See: https://doc.ibexa.co/en/latest/guide/search/solr/#how-to-set-up-solr-search-engine

3. Run integration tests

    To run integration tests against Solr, using [default config](https://github.com/ezsystems/ezplatform-kernel/blob/master/phpunit-integration-legacy-solr.xml#L14-L19):
    ```bash
    export CORES_SETUP="single"
    php -d memory_limit=-1 vendor/bin/phpunit --bootstrap tests/bootstrap.php -vc vendor/ezsystems/ezplatform-kernel/phpunit-integration-legacy-solr.xml
    ```

## Copyright & license

Copyright eZ Systems AS, for copyright and license details see provided LICENSE file.

