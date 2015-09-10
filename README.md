# Solr Search Engine for eZ Platform

[![Build Status](https://travis-ci.org/ezsystems/ezplatform-solr-search-engine.svg?branch=master)](https://travis-ci.org/ezsystems/ezplatform-solr-search-engine)

Solr Search Engine Bundle for use with:
- eZ Platform *(bundled out of the box as of 15.07 release)*
- eZ Publish Platform 5.4.5 *and higher* *(optional, but recommended for scaling search queries)*

Scope for 1.0 version of this bundle is to be able to power close to any kind of queries eZ Publish Platform 5.x users are currently running agains the LegacySearch engine (aka SQL Search/Storage engine), *both* Content and Location Search. This search engine is also taking advantage of Solr's Full text capabilities for language analysis, and it's scalability.

Version 1.0 is tested and will be supported with _Solr 4.10.4_, support for Solr 5.x will be added in a future update.

Other search features such as Faceting, Highlighting, .., not supported by the SQL search engine is planned for future versions, and will be available by a simple composer update, or sometimes need to also just re index your repository.


### Install

For how to Install see:
https://doc.ez.no/display/EZP/Solr+Search+Engine+Bundle
