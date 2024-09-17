FROM solr:8.6.3

LABEL org.opencontainers.image.source=https://github.com/ezsystems/ezplatform-solr-search-engine

USER root
# Note: requires project dir as docker context
# Build using: `docker build -f docker/solr/Dockerfile .` when in project root directory
COPY lib/Resources/config/solr server/solr/configsets/_default/conf
RUN mv server/solr/configsets/_default/conf/schema.xml server/solr/configsets/_default/conf/managed-schema
RUN sed --in-place '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema".*/,/<\/updateRequestProcessorChain>/d' server/solr/configsets/_default/conf/solrconfig.xml
RUN sed --in-place 's/${solr.autoSoftCommit.maxTime:-1}/${solr.autoSoftCommit.maxTime:20}/' server/solr/configsets/_default/conf/solrconfig.xml

USER solr

HEALTHCHECK --interval=10s --timeout=5s --retries=10 --start-period=15s CMD solr status
CMD ["solr-precreate", "collection1"]
