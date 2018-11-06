# Solr Search Engine Bundle for eZ Platform

[![Build Status](https://img.shields.io/travis/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://travis-ci.org/ezsystems/ezplatform-solr-search-engine)
[![Downloads](https://img.shields.io/packagist/dt/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://packagist.org/packages/ezsystems/ezplatform-solr-search-engine)
[![Latest release](https://img.shields.io/github/release/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](https://github.com/ezsystems/ezplatform-solr-search-engine/releases)
[![License](https://img.shields.io/github/license/ezsystems/ezplatform-solr-search-engine.svg?style=flat-square)](LICENSE)

Solr Search Engine Bundle for use with:
- v1.5+: eZ Platform 1.7+ *(bundled out of the box)* with Solr 4.10.4 _or_ 6.x _(recommended: 6.x)_
- v1.0.x: eZ Publish Platform Enterprise 5.4.5+ *(optional, not as feature rich but helpful for scaling filtering queries)* with Solr 4.10.4

#####  Overview of features

| Feature                       | Solr Search Engine        | Legacy Search Engine _(SQL)_ |
|:------------------------------|:-------------------------:|:----------------------------:|
| Filtering                     | Yes                       | Yes, but limited*            |
| Query _(filter with scoring)_ | Yes                       | Only filters, no scoring     |
| Fulltext                      | Yes, incl advance features| Partly**                     |
| Faceting                      | [Partly as of v1.4][1]    | No                           |
| Spellchecking                 | Planned _(TBD when)_      | No                           |
| Highlighting                  | Planned _(TBD when)_      | No                           |
| Index time boosting           | [Yes, as of v1.4][4]      | No                           |

_* Usage of Criterion and SortClause for Fields does not perform well on medium to larger amount of data with Legacy
Search Engine (SQL), use Solr for this._

_** Does not include full set of full text features includes: Scoring/Ranking _(Solr Search Engine does sorting on scoring
by default, for location search available as of [v1.3][3] with Solr 6.x)_, and as of [Solr Search Engine v1.5][5]
supports advanced full text capabilities such as `word "phrase" (group) +mandatory -prohibited AND && OR || NOT !`_

## Install

For how to install and configure see:
- eZ Platform: https://doc.ezplatform.com/en/latest/guide/solr/
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

   E.g. one of the following:
   - [Solr 4.10.4](http://archive.apache.org/dist/lucene/solr/4.10.4/solr-4.10.4.tgz)
   - [Solr 6.6.5](http://archive.apache.org/dist/lucene/solr/6.6.0/solr-6.6.5.tgz)

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
    cd solr-6.6.5
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
    
    Example of working `solrconfig.xml` settings that you can use to tune for your needs, change from defaults is on `autoSoftCommit` from `-1` *(disabled)* to `20`ms:

         <autoCommit> 
           <maxTime>${solr.autoCommit.maxTime:15000}</maxTime> 
           <openSearcher>false</openSearcher> 
         </autoCommit>

         <autoSoftCommit>
           <maxTime>${solr.autoSoftCommit.maxTime:20}</maxTime> 
         </autoSoftCommit>

    ###### TIP for automating generating of config
    You may also use the command line tool `bin/generate-solr-config.sh` to generate the Solr 6 configuration instead of these instructions.
    This is particular convenient if deploying to eZ Platform Cloud (Platform.sh), but can also be used for on-premise installs.

    The script should be executed from the eZ Platform root directory, run the following for more info:

    ```bash
    ./vendor/ezsystems/ezplatform-solr-search-engine/bin/generate-solr-config.sh --help
    ```


4. Start Solr

    ```bash
    # Solr 4.10
    cd solr-4.10.4/example
    java -Djetty.port=8983 -Dsolr.solr.home=multicore -jar start.jar

    # Solr 6
    cd solr-6.6.5
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


[1]: https://doc.ezplatform.com/en/latest/api/public_php_api_browsing/#performing-a-faceted-search
[2]: https://github.com/ezsystems/ezplatform-solr-search-engine
[3]: https://github.com/ezsystems/ezplatform-solr-search-engine/releases/tag/v1.3.0
[4]: https://github.com/ezsystems/ezplatform-solr-search-engine/releases/tag/v1.4.0
[5]: https://github.com/ezsystems/ezplatform-solr-search-engine/releases/tag/v1.5.0