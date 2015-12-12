# Solr Search Engine Bundle for eZ Platform

[![Build Status](https://travis-ci.org/ezsystems/ezplatform-solr-search-engine.svg?branch=master)](https://travis-ci.org/ezsystems/ezplatform-solr-search-engine)

Solr Search Engine Bundle for use with:
- eZ Platform *(bundled out of the box as of 15.07 release)*
- eZ Publish Platform 5.4.5 *and higher* *(optional, but recommended for scaling search queries)*

Scope for 1.0 version of this bundle is to be able to power close to any kind of queries eZ Publish Platform 5.x users are currently running agains the LegacySearch engine *(aka SQL Search/Storage engine)*, *both* Content and Location Search. This search engine is also taking advantage of Solr's Full text capabilities for language analysis, and it's scalability.

Version 1.0 is tested and will be supported with _Solr 4.10.4_, support for Solr 5.x will be added in a future update.

Other search features such as Faceting, Highlighting, .., not supported by the SQL search engine is planned for future versions. Some will be available by a simple composer patch update *(0.0.z)*, for major *(x.0.0)* or minor *(0.y.0)* updates there will sometimes be a need to also re index your repository.


## Install

For how to Install see:
https://doc.ez.no/display/EZP/Solr+Search+Engine+Bundle



### Testing locally

For Contributing to this Bundle, you should make sure to run both unit and integration tests *(from ezpublish-kernel repo)*.

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

2. Get & extract [Solr 4.10.4](http://archive.apache.org/dist/lucene/solr/4.10.4/solr-4.10.4.tgz)

3. Configure Solr *(single core)*

    *Note: See .travis.yml and bin/.travis/init_solr.sh for multi core setups*

    ```bash
    cp -R lib/Resources/config/solr/* solr-4.10.4/example/solr/collection1/conf
    ```

4. Start Solr

    *Note: In this case in seperate terminal for debug use*

    ```bash
    cd solr-4.10.4/example
    java -Djetty.port=8983 -jar start.jar
    ```

5. Run integration tests

    ```bash
    export CORES_SETUP="single"
    php -d memory_limit=-1 vendor/bin/phpunit --bootstrap tests/bootstrap.php -vc vendor/ezsystems/ezpublish-kernel/phpunit-integration-legacy-solr.xml
    ```

## Copyright & license

Copyright eZ Systems AS, for copyright and license details see provided LICENSE file.
