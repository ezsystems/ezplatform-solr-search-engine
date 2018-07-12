#!/usr/bin/env bash

set -e

# Default paramters, if not overloaded by user arguments
DESTINATION_DIR=.platform/configsets/solr6/conf
SOLR_VERSION=6.6.5
FORCE=false
SOLR_INSTALL_DIR=""

show_help() {
    cat << EOF
Script for generting solr config
This config can be used to configure solr on eZ Platform Cloud (Platform.sh) or elsewhere.
The script should be executed from the eZ Platform root directory.

Help (this text):
./vendor/ezsystems/ezplatform-solr-search-engine/bin/generate-solr-config.sh --help

Usage with eZ Platform Cloud (arguments here can be skipped as they have default values):
./vendor/ezsystems/ezplatform-solr-search-engine/bin/generate-solr-config.sh \\
  --destination-dir=.platform/configsets/solr6/conf \\
  --solr-version=6.6.5

Usage with on-premise version of Solr:
./vendor/ezsystems/ezplatform-solr-search-engine/bin/generate-solr-config.sh \\
  --destination-dir=/opt/solr/server/ez/template \\
  --solr-install-dir=/opt/solr

Warning:
 This script only supports Solr 6 and higher !!


Arguments:
  [--destination-dir=<dest.dir>]     : Location where solr config should be stored
                                       Default value is .platform/configsets/solr6/conf
  [-f|--force]                       : Overwrite destination-dir if it already exists
  [--solr-install-dir]               : Existing downloaded Solr install to copy base config from.
  [--solr-version]                   : Solr version to download & copy base config from, used only if --solr-install-dir is unset
  [-h|--help]                        : Help text (this text)
EOF
}

EZ_BUNDLE_PATH="`dirname $0`/../../../vendor/ezsystems/ezplatform-solr-search-engine"

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
        --solr-install-dir=*)
            SOLR_INSTALL_DIR="${i#*=}"
            SOLR_INSTALL_DIR="${SOLR_INSTALL_DIR/#\~/$HOME}"
            ;;
        # Internal argument for use with Travis tests only
        --ez-bundle-path=*)
            EZ_BUNDLE_PATH="${i#*=}"
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
        echo -e "\033[0;31mDestination directory ($DESTINATION_DIR) already exists, removing in 5 seconds.... \033[0m"
        sleep 5
        rm -Rf $DESTINATION_DIR
    else
        echo -e "\033[1;31mError: Destination dir already exists ($DESTINATION_DIR). Use -f parameter to force \033[0m"
        exit 1
    fi
fi

if [ "$SOLR_INSTALL_DIR" == "" ]; then
    # If we where not provided existing install directory we'll temporary download version of solr 6 to generate config.
    GENERATE_SOLR_TMPDIR=`mktemp -d`
    echo "Downloading solr bundle:"
    curl http://archive.apache.org/dist/lucene/solr/${SOLR_VERSION}/solr-${SOLR_VERSION}.tgz > $GENERATE_SOLR_TMPDIR/solr-${SOLR_VERSION}.tgz

    echo "Untaring"
    cd $GENERATE_SOLR_TMPDIR
    tar -xzf solr-${SOLR_VERSION}.tgz
    cd - > /dev/null 2>&1
    echo "done extracting Solr"
    SOLR_INSTALL_DIR="${GENERATE_SOLR_TMPDIR}/solr-${SOLR_VERSION}"
fi

mkdir -p $DESTINATION_DIR
cp -a ${EZ_BUNDLE_PATH}/lib/Resources/config/solr/* $DESTINATION_DIR
cp ${SOLR_INSTALL_DIR}/server/solr/configsets/basic_configs/conf/{currency.xml,solrconfig.xml,stopwords.txt,synonyms.txt,elevate.xml} $DESTINATION_DIR

if [[ ! $DESTINATION_DIR =~ ^\.platform ]]; then
    # If we are not targeting .platform(.sh) config, we also output default solr.xml
    cp -f ${SOLR_INSTALL_DIR}/server/solr/solr.xml $DESTINATION_DIR/..
else
    echo "NOTE: Skipped copying ${SOLR_INSTALL_DIR}/server/solr/solr.xml given destination dir is a '.platform/' config folder"
fi

# Adapt autoSoftCommit to have a recommended value, and remove add-unknown-fields-to-the-schema
sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema">/,/<\/updateRequestProcessorChain>/d' $DESTINATION_DIR/solrconfig.xml
sed -i.bak2 's/${solr.autoSoftCommit.maxTime:-1}/${solr.autoSoftCommit.maxTime:20}/' $DESTINATION_DIR/solrconfig.xml

if [ "$GENERATE_SOLR_TMPDIR" != "" ]; then
    echo Removing temp dir: $GENERATE_SOLR_TMPDIR
    rm -Rf ${GENERATE_SOLR_TMPDIR}
fi

echo -e "\033[0;32mDone generating config to $DESTINATION_DIR ! \033[0m"

if [[ ! $DESTINATION_DIR =~ ^\.platform ]]; then
    echo "NOTE: You also need to enable solr service in `.platform.app.yaml` and `.platform/services.yaml`."
fi
