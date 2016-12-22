#!/usr/bin/env bash

default_config_files[1]='lib/Resources/config/solr/schema.xml'
default_config_files[2]='lib/Resources/config/solr/custom-fields-types.xml'
default_config_files[3]='lib/Resources/config/solr/language-fieldtypes.xml'

default_nodes=('node1:8983' 'node2:8984' 'node3:8985')
default_shards=('shard0' 'shard1' 'shard2' 'shard3')

SOLR_DIR=${SOLR_DIR:-'__solr'}
SOLR_PORT=${SOLR_PORT:-'8983'}
SOLR_VERSION=${SOLR_VERSION:-'6.3.0'}
SOLR_HOME=${SOLR_HOME:-'ezcloud'}
SOLR_CONFIG=("${SOLR_CONFIG[@]:-${default_config_files[*]}}")
SOLR_SHARDS=("${SOLR_SHARDS[@]:-${default_shards[*]}}")
SOLR_NODES=("${SOLR_NODES[@]:-${default_nodes[@]}}")
SOLR_COLLECTION_NAME=${SOLR_COLLECTION_NAME:-'ezplatform'}
SOLR_CONFIGURATION_NAME=${SOLR_CONFIGURATION_NAME:-'ezconfig'}
SOLR_MAX_SHARDS_PER_NODE=${SOLR_MAX_SHARDS_PER_NODE:-'3'}
SOLR_REPLICATION_FACTOR=${SOLR_REPLICATION_FACTOR:-'2'}

INSTALL_DIR="${SOLR_DIR}/${SOLR_VERSION}"
HOME_DIR="${INSTALL_DIR}/server/${SOLR_HOME}"
TEMPLATE_DIR="${HOME_DIR}/template"
START_SCRIPT="./${INSTALL_DIR}/bin/solr"
ZOOKEEPER_CLI_SCRIPT="./${INSTALL_DIR}/server/scripts/cloud-scripts/zkcli.sh"
ZOOKEEPER_HOST=""

download() {
    case ${SOLR_VERSION} in
        6.3.0 )
            url="http://archive.apache.org/dist/lucene/solr/${SOLR_VERSION}/solr-${SOLR_VERSION}.tgz"
            ;;
        *)
            echo "Version '${SOLR_VERSION}' is not supported or not valid"
            exit 1
            ;;
    esac

    create_dir ${SOLR_DIR}

    local archive_file_name="${SOLR_VERSION}.tgz"
    local installation_archive_file="${SOLR_DIR}/${archive_file_name}"

    if [ ! -d ${INSTALL_DIR} ] ; then
        echo "Installation ${SOLR_VERSION} does not exists"

        if [ ! -f ${installation_archive_file} ] ; then
            echo "Installation archive ${archive_file_name} does not exist"
            echo "Downloading Solr from ${url}..."
            curl -o ${installation_archive_file} ${url}
            echo 'Downloaded'
        fi

        echo "Extracting from installation archive ${archive_file_name}..."
        create_dir ${INSTALL_DIR}
        tar -zxf ${installation_archive_file} -C ${INSTALL_DIR} --strip-components=1
        echo 'Extracted'
    else
        echo "Found existing ${SOLR_VERSION} installation"
    fi
}

copy_files() {
    local destination_dir_name=$1
    shift
    local files="$@"

    for file in ${files} ; do
        copy_file ${file} ${destination_dir_name}
    done
}

copy_file() {
    local file=$1
    local destination_dir=$2

    if [ -f ${file} ] ; then
        cp ${file} ${destination_dir} || exit_on_error "Couldn't copy file '${file}' to directory '${destination_dir}'"
        echo "Copied file '${file}' to directory '${destination_dir}'"
    else
        echo "${file} is not valid"
        exit 1
    fi
}

create_dir() {
    local dir_name=$1

    if [ ! -d ${dir_name} ] ; then
        mkdir ${dir_name} || exit_on_error "Couldn't create directory '${dir_name}'"
        echo "Created directory '${dir_name}'"
    fi
}

exit_on_error() {
    local message=$1

    echo "ERROR: ${message}"
    exit 1
}

configure_nodes() {
    create_dir ${HOME_DIR}

    for node in "${SOLR_NODES[@]}" ; do
        IFS=':' read node_name node_port <<< "${node}"

        if [ ! -d "${HOME_DIR}/${node_name}" ] ; then
            configure_node ${node_name} ${node_port}
        else
            echo "Node '${node_name}' already exists, skipping"
        fi
    done
}

configure_node() {
    local name=$1
    local port=$2
    local node_dir="${HOME_DIR}/${name}"

    create_dir ${node_dir}
    copy_file "${INSTALL_DIR}/server/solr/zoo.cfg" ${node_dir}
    copy_file "${INSTALL_DIR}/server/solr/solr.xml" ${node_dir}

    # define host name 'localhost'
    sed -i.bak "s/\${host:}/\${host:localhost}/g" "${node_dir}/solr.xml" || exit_on_error "Can't modify file '${node_dir}/solr.xml'"
    # define port
    sed -i.bak "s/\${jetty.port:8983}/\${jetty.port:${port}}/g" "${node_dir}/solr.xml" || exit_on_error "Can't modify file '${node_dir}/solr.xml'"
}

start_nodes() {
    for node in "${SOLR_NODES[@]}" ; do
        local IFS=':'; read node_name node_port <<< "${node}"
        local node_dir="${HOME_DIR}/${node_name}"

        if [[ ! ${ZOOKEEPER_HOST} ]] ; then
            ${START_SCRIPT} start -cloud -s ${node_dir} -p ${node_port} -V || exit_on_error "Can't start node '${node_name}'"
            # start script default
            ZOOKEEPER_HOST="localhost:$((node_port+1000))"
        else
            ${START_SCRIPT} start -cloud -s ${node_dir} -p ${node_port} -z "${ZOOKEEPER_HOST}" -V || exit_on_error "Can't start node '${node_name}'"
        fi
    done
}

configure_collection() {
    if [ -d ${TEMPLATE_DIR} ] ; then
        echo "Configuration template is already created, skipping..."
        return 1
    fi

    create_dir ${HOME_DIR}
    create_dir ${TEMPLATE_DIR}

    local files=("${SOLR_CONFIG[@]}")
    local config_dir="${INSTALL_DIR}/server/solr/configsets/basic_configs/conf"

    files+=("${config_dir}/currency.xml")
    files+=("${config_dir}/stopwords.txt")
    files+=("${config_dir}/synonyms.txt")
    files+=("${config_dir}/elevate.xml")
    files+=("${config_dir}/solrconfig.xml")

    copy_files ${TEMPLATE_DIR} "${files[*]}"

    # modify solrconfig.xml to remove section that doesn't agree with our schema
    sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema">/,/<\/updateRequestProcessorChain>/d' "${TEMPLATE_DIR}/solrconfig.xml" || exit_on_error "Can't modify file '${TEMPLATE_DIR}/solrconfig.xml'"
}

upload_collection_configuration() {
    ${ZOOKEEPER_CLI_SCRIPT} -zkhost "${ZOOKEEPER_HOST}" -cmd upconfig -confname ${SOLR_CONFIGURATION_NAME} -confdir ${TEMPLATE_DIR} || exit_on_error "Can't upload configuration to Zookeeper"
    echo "Uploaded configuration to Zookeeper"
}

create_collection() {
    local shards="${SOLR_SHARDS[@]// /,}"
    local nodes_tmp=("${SOLR_NODES[@]/*:/localhost:}")
    local nodes="${nodes_tmp[@]/%/_solr}"
    nodes="${nodes[@]// /,}"

    local parameters=(
        "name=${SOLR_COLLECTION_NAME}"
        "collection.configName=${SOLR_CONFIGURATION_NAME}"
        "createNodeSet=${nodes}"
        "shards=${shards}"
        "maxShardsPerNode=${SOLR_MAX_SHARDS_PER_NODE}"
        "replicationFactor=${SOLR_REPLICATION_FACTOR}"
        "router.name=implicit"
        "router.field=router_field_id"
        "wt=json"
        "indent=on"
    )

    echo "Creating collection with parameters:"
    echo "$(IFS=$'\n'; echo "${parameters[*]}")"

    local url="http://localhost:8983/solr/admin/collections?action=CREATE&$(IFS=$'&'; echo "${parameters[*]}")"

    curl ${url} || exit_on_error "Couldn't create collection"
}

download
configure_nodes
start_nodes
configure_collection
upload_collection_configuration
create_collection
