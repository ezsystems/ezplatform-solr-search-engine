FROM solr:8.6.3

LABEL org.opencontainers.image.source=https://github.com/ezsystems/ezplatform-solr-search-engine

USER root
COPY conf server/solr/configsets/_default/conf
RUN sed --in-place '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema".*/,/<\/updateRequestProcessorChain>/d' server/solr/configsets/_default/conf/solrconfig.xml
RUN sed --in-place 's/${solr.autoSoftCommit.maxTime:-1}/${solr.autoSoftCommit.maxTime:20}/' server/solr/configsets/_default/conf/solrconfig.xml

USER solr

CMD ["solr-precreate", "collection1"]
