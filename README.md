# Solr Search Engine Bundle for eZ Platform

[![Build Status](https://img.shields.io/travis/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://travis-ci.org/ezsystems/ezplatform-solr-search-engine)
[![Downloads](https://img.shields.io/packagist/dt/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://packagist.org/packages/ezsystems/ezplatform-solr-search-engine)
[![Latest release](https://img.shields.io/github/release/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://github.com/ezsystems/ezplatform-solr-search-engine/releases)
[![License](https://img.shields.io/packagist/l/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](LICENSE)

Solr Search Engine Bundle for use with:
- eZ Platform *(bundled out of the box as of 15.07 release)*
- eZ Publish Platform 5.4.5 *and higher* *(optional, but recommended for scaling search queries)*

Scope for 1.0 version of this bundle is to be able to power close to any kind of queries eZ Publish Platform 5.x users are currently running agains the LegacySearch engine *(aka SQL Search/Storage engine)*, *both* Content and Location Search. This search engine is also taking advantage of Solr's Full text capabilities for language analysis, and it's scalability.

1.1 and higher has dendencies on changes in eZ Platform, and thus 1.0.x is kept supported for eZ Publish 5.4 users.

Version 1.0-1.2 is tested and supported with _Solr 4.10.4_, 1.3 adds support for Solr 6 _(tested with 6.4.2)_.

Other search features such as Faceting, Highlighting, .., not supported by the SQL search engine is planned for future versions. Some will be available by a simple composer patch update *(0.0.z)*. For major *(x.0.0)* or minor *(0.y.0)* updates there will sometimes be a need to also re index your repository.


## Install

For how to install and configure see:
- eZ Platform: https://doc.ez.no/display/DEVELOPER/Solr+Bundle
- eZ Publish Platform 5.4.x: https://doc.ez.no/display/EZP/Solr+Search+Engine+Bundle



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

2. Get & extract Solr

   One of the following:
   - [Solr 4.10.4](http://archive.apache.org/dist/lucene/solr/4.10.4/solr-4.10.4.tgz)
   - [Solr 6.6.0](http://archive.apache.org/dist/lucene/solr/6.6.0/solr-6.6.0.tgz)

3. Configure Solr *(single core)*

    *Note: See .travis.yml and bin/.travis/init_solr.sh for multi core setups*

    ```bash
    # Solr 4.10
    cd solr-4.10.4/example
    mkdir -p multicore/collection1/conf
    cp -R <ezplatform-solr-search-engine>/lib/Resources/config/solr/* multicore/collection1/conf
    cp solr/collection1/conf/{currency.xml,stopwords.txt,synonyms.txt} multicore/collection1/conf
    ## Remove default cores configuration and add core configuration
    sed -i.bak 's/<core name=".*" instanceDir=".*" \/>//g' multicore/solr.xml
    sed -i.bak "s/<shardHandlerFactory/<core name=\"collection1\" instanceDir=\"collection1\" \/><shardHandlerFactory/g" multicore/solr.xml
    cp multicore/core0/conf/solrconfig.xml multicore/collection1/conf
    sed -i.bak s/core0/collection1/g multicore/collection1/conf/solrconfig.xml

    # Solr 6
    cd solr-6
    mkdir -p server/ez/template
    cp -R <ezplatform-solr-search-engine>/lib/Resources/config/solr/* server/ez/template
    cp server/solr/configsets/basic_configs/conf/{currency.xml,solrconfig.xml,stopwords.txt,synonyms.txt,elevate.xml} server/ez/template
    cp server/solr/solr.xml server/ez
    ## Modify solrconfig.xml to remove section that doesn't agree with our schema
    sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema">/,/<\/updateRequestProcessorChain>/d' server/ez/template/solrconfig.xml
    ```
    
    ###### For use in production/dev
    Note that Solr Bundle does not commit changes directly on repository updates,
    which can lead to issues of content not showing up in the index. You can control this by adjusting `autoSoftCommit` *(visibility
    of change to search index)* and `autoCommit` *(hard commit, for durability and replication)* values in `solrconfig.xml`.
    
    Example of working `solrconfig.xml` settings that you can use to tune for your needs, change from defaults is on `autoSoftCommit` from `-1` *(disabled)* to `100`ms:

         <autoCommit> 
           <maxTime>${solr.autoCommit.maxTime:15000}</maxTime> 
           <openSearcher>false</openSearcher> 
         </autoCommit>

         <autoSoftCommit>
           <maxTime>${solr.autoSoftCommit.maxTime:100}</maxTime> 
         </autoSoftCommit>


4. Start Solr

    ```bash
    # Solr 4.10
    cd solr-4.10.4/example
    java -Djetty.port=8983 -Dsolr.solr.home=multicore -jar start.jar

    # Solr 6
    cd solr-6
    bin/solr -s ez
    ## You'll also need to add cores on Solr 6, this adds single core setup:
    bin/solr create_core -c collection1 -d server/ez/template
    ```

5. Run integration tests

    ```bash
    export CORES_SETUP="single"
    php -d memory_limit=-1 vendor/bin/phpunit --bootstrap tests/bootstrap.php -vc vendor/ezsystems/ezpublish-kernel/phpunit-integration-legacy-solr.xml
    ```

## Copyright & license

Copyright eZ Systems AS, for copyright and license details see provided LICENSE file.
