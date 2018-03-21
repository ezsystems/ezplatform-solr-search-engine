#!/bin/bash

set -e

DESTINATION_DIR=.platform/configsets/solr6/conf
SOLR_VERSION=6.6.0
FORCE=false

function show_help
{
    cat << EOF
Script for solr config
This config can be used to configure solr on Platform.sh or elsewhere.
The script should be executed from the eZ Platform root directory.

Help (this text):
./vendor/ezsystems/ezplatform-solr-search-engine/bin/generate-solr-config.sh --help

Usage:
./vendor/ezsystems/ezplatform-solr-search-engine/bin/generate-solr-config.sh \\
  --destination-dir=.platform/configsets/solr6/conf


Arguments:
  [--destination-dir=<dest.dir>]           : Location where solr config should be stored
                                             Default value is .platform/configsets/solr6/conf
  [-f|--force]                             : Overwrite destination-dir if it already exists
  [--solr-version]                         : What solr version to copy base config from
                                             ATM, only 6.6.0 is officially supported
  [-h|--help]                              : Help text, this one more or less

EOF
}


## Parse arguments
for i in "$@"; do
    case $i in
        --destination-dir=*)
            DESTINATION_DIR="${i#*=}"
            ;;
        -f|--force)
            FORCE=true
            ;;
        --solr-version=*)
            SOLR_VERSION="${i#*=}"
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            show_help "${i}"
            exit 1
            ;;
    esac
done

if [ `whoami` == "root" ]; then
    echo "Error : Do not run this script as root"
    exit 1
fi

if [ -e $DESTINATION_DIR ]; then
    if [ "$FORCE" == "true" ]; then
        echo "Destination directory ($DESTINATION_DIR) already exists, removing...."
        sleep 5
        rm -Rf $DESTINATION_DIR
    else
        echo "Error : Destination dir already exists : $DESTINATION_DIR. Use -f parameter to force"
        exit 1
    fi
fi


TMPDIR=`mktemp --tmpdir=/tmp -d`
SCRIPT=`realpath $0`
EZPPATH="`dirname $SCRIPT`/../../../"

echo "Downloading solr bundle:"
curl http://archive.apache.org/dist/lucene/solr/${SOLR_VERSION}/solr-${SOLR_VERSION}.tgz > $TMPDIR/solr-${SOLR_VERSION}.tgz

echo Untaring
cd $TMPDIR
tar -xzf solr-${SOLR_VERSION}.tgz
cd - > /dev/null 2>&1
echo done

mkdir -p $DESTINATION_DIR
cp -a ${EZPPATH}vendor/ezsystems/ezplatform-solr-search-engine/lib/Resources/config/solr/* $DESTINATION_DIR
cp ${TMPDIR}/solr-${SOLR_VERSION}/server/solr/configsets/basic_configs/conf/currency.xml $DESTINATION_DIR
cp ${TMPDIR}/solr-${SOLR_VERSION}/server/solr/configsets/basic_configs/conf/solrconfig.xml $DESTINATION_DIR
cp ${TMPDIR}/solr-${SOLR_VERSION}/server/solr/configsets/basic_configs/conf/stopwords.txt $DESTINATION_DIR
cp ${TMPDIR}/solr-${SOLR_VERSION}/server/solr/configsets/basic_configs/conf/synonyms.txt $DESTINATION_DIR
cp ${TMPDIR}/solr-${SOLR_VERSION}/server/solr/configsets/basic_configs/conf/elevate.xml $DESTINATION_DIR
# FYI : Next one is not used by platform.sh, but we'll put it there anyway:
cp ${TMPDIR}/solr-${SOLR_VERSION}/server/solr/solr.xml $DESTINATION_DIR/ez
sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema">/,/<\/updateRequestProcessorChain>/d' $DESTINATION_DIR/solrconfig.xml
sed -i.bak2 's/${solr.autoSoftCommit.maxTime:-1}/${solr.autoSoftCommit.maxTime:100}/' $DESTINATION_DIR/solrconfig.xml

echo Removing temp dir : $TMPDIR
rm -Rf ${TMPDIR}
