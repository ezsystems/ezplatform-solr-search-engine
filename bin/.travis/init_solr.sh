#!/usr/bin/env bash

default_config_files[1]='lib/Resources/config/solr/schema.xml'
default_config_files[2]='lib/Resources/config/solr/custom-fields-types.xml'
default_config_files[3]='lib/Resources/config/solr/language-fieldtypes.xml'

default_cores[0]='core0'
default_cores[1]='core1'
default_cores[2]='core2'
default_cores[3]='core3'

SOLR_PORT=${SOLR_PORT:-8983}
SOLR_VERSION=${SOLR_VERSION:-6.4.1}
SOLR_DEBUG=${SOLR_DEBUG:-false}
SOLR_HOME=${SOLR_HOME:-ez}
SOLR_CONFIG=${SOLR_CONFIG:-${default_config_files[*]}}
SOLR_CORES=${SOLR_CORES:-${default_cores[*]}}
SOLR_DIR=${SOLR_DIR:-__solr}
SOLR_INSTALL_DIR="${SOLR_DIR}/${SOLR_VERSION}"

download() {
    case ${SOLR_VERSION} in
        # PS!!: Append versions and don't remove old once, kernel uses this script!
        4.10.4|6.3.0|6.4.1 )
            url="http://archive.apache.org/dist/lucene/solr/${SOLR_VERSION}/solr-${SOLR_VERSION}.tgz"
            ;;
        *)
            echo "Version '${SOLR_VERSION}' is not supported or not valid"
            exit 1
            ;;
    esac

    create_dir ${SOLR_DIR}

    archive_file_name="${SOLR_VERSION}.tgz"
    installation_archive_file="${SOLR_DIR}/${archive_file_name}"

    if [ ! -d ${SOLR_INSTALL_DIR} ] ; then
        echo "Installation ${SOLR_VERSION} does not exists"

        if [ ! -f ${installation_archive_file} ] ; then
            echo "Installation archive ${archive_file_name} does not exist"
            echo "Downloading Solr from ${url}..."
            curl -o ${installation_archive_file} ${url}
            echo 'Downloaded'
        fi

        echo "Extracting from installation archive ${archive_file_name}..."
        create_dir ${SOLR_INSTALL_DIR}
        tar -zxf ${installation_archive_file} -C ${SOLR_INSTALL_DIR} --strip-components=1
        echo 'Extracted'
    else
        echo "Found existing ${SOLR_VERSION} installation"
    fi
}

copy_files() {
    destination_dir_name=$1
    shift
    files=("$@")

    for file in ${files} ; do
        copy_file ${file} ${destination_dir_name}
    done
}

copy_file() {
    file=$1
    destination_dir=$2

    if [ -f "${file}" ] ; then
        cp ${file} ${destination_dir}
        echo "Copied file '${file}' to directory '${destination_dir}'"
    else
        echo "${file} is not valid"
        exit 1
    fi
}

create_dir() {
    dir_name=$1

    if [ ! -d ${dir_name} ] ; then
        mkdir ${dir_name}
        echo "Created directory '${dir_name}'"
    fi
}

exit_on_error() {
    message=$1

    echo "ERROR: ${message}"
    exit 1
}

is_solr_up() {
    address="http://localhost:${SOLR_PORT}/solr/admin/cores"
    http_code=`echo $(curl -s -o /dev/null -w "%{http_code}" ${address})`
    echo "Checking if Solr is up on ${address}"
    return `test ${http_code} = "200"`
}

wait_for_solr(){
    while ! is_solr_up; do
        sleep 3
    done
}

solr4_configure() {
    # remove default cores configuration
    sed -i.bak 's/<core name=".*" instanceDir=".*" \/>//g' "${SOLR_INSTALL_DIR}/example/multicore/solr.xml"

    for solr_core in ${SOLR_CORES} ; do
        solr4_add_core ${solr_core}
    done
}

solr4_add_core() {
    solr_core=$1
    core_dir="${SOLR_INSTALL_DIR}/example/multicore/${solr_core}"
    core_conf_dir="${core_dir}/conf"
    config_dir="${SOLR_INSTALL_DIR}/example/solr/collection1/conf"

    # add core configuration
    sed -i.bak "s/<shardHandlerFactory/<core name=\"$solr_core\" instanceDir=\"$solr_core\" \/><shardHandlerFactory/g" ${SOLR_INSTALL_DIR}/example/multicore/solr.xml

    # prepare core directories
    create_dir ${core_dir}
    create_dir ${core_conf_dir}

    files=${SOLR_CONFIG}
    files+=("${config_dir}/currency.xml")
    files+=("${config_dir}/stopwords.txt")
    files+=("${config_dir}/synonyms.txt")

    copy_files ${core_conf_dir} "${files[*]}"

    # copy core0 solrconfig.xml and patch it for current core
    if [ ! -f ${core_conf_dir}/solrconfig.xml ] ; then
        copy_file "${SOLR_INSTALL_DIR}/example/multicore/core0/conf/solrconfig.xml" ${core_conf_dir}
        sed -i.bak s/core0/"${solr_core}"/g ${core_conf_dir}/solrconfig.xml
    fi

    echo "Configured core ${solr_core}"
}

solr4_run() {
    echo "Running with version ${SOLR_VERSION}"
    echo "Starting solr on port ${SOLR_PORT}..."

    cd ${SOLR_INSTALL_DIR}/example

    if [ "$SOLR_DEBUG" = "true" ] ; then
        java -Djetty.port=${SOLR_PORT} -Dsolr.solr.home=multicore -jar start.jar &
    else
        java -Djetty.port=${SOLR_PORT} -Dsolr.solr.home=multicore -jar start.jar > /dev/null 2>&1 &
    fi

    wait_for_solr

    cd ../../../
    echo 'Started'
}

# Configure for Solr 6, see solr4_configure() for 4.10
configure() {
    home_dir="${SOLR_INSTALL_DIR}/server/${SOLR_HOME}"
    template_dir="${home_dir}/template"
    config_dir="${SOLR_INSTALL_DIR}/server/solr/configsets/basic_configs/conf"

    create_dir ${home_dir}
    create_dir ${template_dir}

    files=${SOLR_CONFIG}
    files+=("${config_dir}/currency.xml")
    files+=("${config_dir}/solrconfig.xml")
    files+=("${config_dir}/stopwords.txt")
    files+=("${config_dir}/synonyms.txt")
    files+=("${config_dir}/elevate.xml")

    copy_files ${template_dir} "${files[*]}"
    copy_file "${SOLR_INSTALL_DIR}/server/solr/solr.xml" ${home_dir}

    # modify solrconfig.xml to remove section that doesn't agree with our schema
    sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema">/,/<\/updateRequestProcessorChain>/d' "${template_dir}/solrconfig.xml"
}

# Run for Solr 6, see solr4_run() for 4.10
run() {
    echo "Running with version ${SOLR_VERSION} in standalone mode"
    echo "Starting solr on port ${SOLR_PORT}..."

    ./${SOLR_INSTALL_DIR}/bin/solr -p ${SOLR_PORT} -s ${SOLR_HOME} || exit_on_error "Can't start Solr"

    echo "Started"

    create_cores
}

# Create cores for Solr 6, see solr4_add_core() for 4.10
create_cores() {
    home_dir="${SOLR_INSTALL_DIR}/server/${SOLR_HOME}"
    template_dir="${home_dir}/template"

    for solr_core in ${SOLR_CORES} ; do
        if [ ! -d "${home_dir}/${solr_core}" ] ; then
            create_core ${solr_core} ${template_dir}
        else
            echo "Core ${solr_core} already exists, skipping"
        fi
    done
}

create_core() {
    core_name=$1
    config_dir=$2

    ./${SOLR_INSTALL_DIR}/bin/solr create_core -c ${core_name} -d ${config_dir} || exit_on_error "Can't create core"
}

download

if [[ ${SOLR_VERSION} == 6* ]] ; then
    configure
    run
else
    solr4_configure
    solr4_run
fi
