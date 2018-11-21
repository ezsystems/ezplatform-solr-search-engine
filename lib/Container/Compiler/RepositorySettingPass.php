<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RepositorySettingPass implements CompilerPassInterface
{
    /**
     * Configured core gateway service ID.
     *
     * @var string
     */
    const GATEWAY_SOLR_ID = 'ezpublish.search.solr.gateway.native';

    /**
     * Configured core gateway service ID.
     *
     * @var string
     */
    const GATEWAY_SOLR_CLOUD_ID = 'ezpublish.search.solr.gateway.solr_cloud';

    /**
     * Configured core filter service ID.
     *
     * @var string
     */
    const CORE_FILTER_ID = 'ezpublish.search.solr.core_filter.native';

    /**
     * Configured core endpoint resolver service ID.
     *
     * @var string
     */
    const ENDPOINT_RESOLVER_ID = 'ezpublish.search.solr.gateway.endpoint_resolver.native';

    public function process(ContainerBuilder $container)
    {
        $provider = $container->get('ezpublish.api.repository_configuration_provider');
        $repositoryConfiguration = $provider->getRepositoryConfig();
        $engine = $repositoryConfiguration['search']['engine'];

        if( $engine == 'solrcloud') {
            $container->setAlias('ezpublish.search.solr.gateway', self::GATEWAY_SOLR_CLOUD_ID);
        } else {
            $container->setAlias('ezpublish.search.solr.gateway', self::GATEWAY_SOLR_ID);
        }

        $connections = $container->getParameter('ez_search_engine_solr.connections');

        foreach ($connections as $name => $params) {
            $this->configureSearchService($container, $name, $params, $engine);
        }
    }

    /**
     * Creates needed search services for given connection name and parameters.
     *
     * @param ContainerBuilder $container
     * @param string $connectionName
     * @param array $connectionParams
     * @param string $engine
     */
    protected function configureSearchService($container, $connectionName, $connectionParams, $engine)
    {
        $alias = 'ez_search_engine_solr';

        if( $engine == 'solrcloud') {
            $gatewayServiceName = self::GATEWAY_SOLR_CLOUD_ID;
        } else {
            $gatewayServiceName = self::GATEWAY_SOLR_ID;
        }

        // Endpoint resolver
        $endpointResolverDefinition = $container->getDefinition(self::ENDPOINT_RESOLVER_ID);
        $endpointResolverDefinition->replaceArgument(0, $connectionParams['entry_endpoints']);
        $endpointResolverDefinition->replaceArgument(1, $connectionParams['mapping']['translations']);
        $endpointResolverDefinition->replaceArgument(2, $connectionParams['mapping']['default']);
        $endpointResolverDefinition->replaceArgument(3, $connectionParams['mapping']['main_translations']);
        $endpointResolverId = "$alias.connection.$connectionName.endpoint_resolver_id";
        $container->setDefinition($endpointResolverId, $endpointResolverDefinition);

        // Core filter
        $coreFilterDefinition = $container->getDefinition(self::CORE_FILTER_ID);
        $coreFilterDefinition->replaceArgument(0, new Reference($endpointResolverId));
        $coreFilterId = "$alias.connection.$connectionName.core_filter_id";
        $container->setDefinition($coreFilterId, $coreFilterDefinition);

        // Gateway
        $gatewayDefinition = $container->getDefinition($gatewayServiceName);
        $gatewayDefinition->replaceArgument(1, new Reference($endpointResolverId));
        $gatewayId = "$alias.connection.$connectionName.gateway_id";
        $container->setDefinition($gatewayId, $gatewayDefinition);
    }
}
