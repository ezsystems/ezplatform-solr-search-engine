<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngineBundle\DependencyInjection;

use Ibexa\Solr\Gateway\UpdateSerializerInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * @phpstan-type SolrHttpClientConfigArray = array{timeout: int, max_retries: int}
 */
class EzSystemsEzPlatformSolrSearchEngineExtension extends Extension
{
    /**
     * Main Solr search handler service ID.
     *
     * @var string
     */
    const ENGINE_ID = 'ezpublish.spi.search.solr';

    /**
     * Configured core gateway service ID.
     *
     * Not using service alias since alias can't be passed for decoration.
     *
     * @var string
     */
    const GATEWAY_ID = 'ezpublish.search.solr.gateway.native';

    /**
     * Configured core filter service ID.
     *
     * Not using service alias since alias can't be passed for decoration.
     *
     * @var string
     */
    const CORE_FILTER_ID = 'ezpublish.search.solr.core_filter.native';

    /**
     * Configured core endpoint resolver service ID.
     *
     * Not using service alias since alias can't be passed for decoration.
     *
     * @var string
     */
    const ENDPOINT_RESOLVER_ID = 'ezpublish.search.solr.gateway.endpoint_resolver.native';

    /**
     * Endpoint class.
     *
     * @var string
     */
    const ENDPOINT_CLASS = 'EzSystems\\EzPlatformSolrSearchEngine\\Gateway\\Endpoint';

    /**
     * Endpoint service tag.
     *
     * @var string
     */
    const ENDPOINT_TAG = 'ezpublish.search.solr.endpoint';

    /**
     * @var string
     */
    const BOOST_FACTOR_PROVIDER_ID = 'ezpublish.search.solr.field_mapper.boost_factor_provider';

    /**
     * @var string
     */
    const STANDALONE_DISTRIBUTION_STRATEGY_ID = 'ezpublish.search.solr.gateway.distribution_strategy.abstract_standalone';

    /**
     * @var string
     */
    const CLOUD_DISTRIBUTION_STRATEGY_ID = 'ezpublish.search.solr.gateway.distribution_strategy.abstract_cloud';

    public const GATEWAY_UPDATE_SERIALIZER_TAG = 'ibexa.solr.gateway.serializer.update';

    public function getAlias()
    {
        return 'ez_search_engine_solr';
    }

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        // Loading configuration from lib/Resources/config/container
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../lib/Resources/config/container')
        );
        $loader->load('solr.yml');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        $this->processConnectionConfiguration($container, $config);

        $container
            ->registerForAutoconfiguration(UpdateSerializerInterface::class)
            ->addTag(self::GATEWAY_UPDATE_SERIALIZER_TAG);
    }

    /**
     * Processes connection configuration by flattening connection parameters
     * and setting them to the container as parameters.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    protected function processConnectionConfiguration(ContainerBuilder $container, array $config)
    {
        $alias = $this->getAlias();

        if (isset($config['default_connection'])) {
            $container->setParameter(
                "{$alias}.default_connection",
                $config['default_connection']
            );
        } elseif (!empty($config['connections'])) {
            reset($config['connections']);
            $container->setParameter(
                "{$alias}.default_connection",
                key($config['connections'])
            );
        }

        foreach ($config['connections'] as $name => $params) {
            $this->configureSearchServices($container, $name, $params);
            $this->configureBoostMap($container, $name, $params);
            $this->configureIndexingDepth($container, $name, $params);

            $container->setParameter("$alias.connection.$name", $params);
        }

        foreach ($config['endpoints'] as $name => $params) {
            $this->defineEndpoint($container, $name, $params);
        }

        // Search engine itself, for given connection name
        $searchEngineDef = $container->findDefinition(self::ENGINE_ID);
        $searchEngineDef->setFactory([new Reference('ezpublish.solr.engine_factory'), 'buildEngine']);

        // Factory for BoostFactorProvider uses mapping configured for the connection in use
        $boostFactorProviderDef = $container->findDefinition(self::BOOST_FACTOR_PROVIDER_ID);
        $boostFactorProviderDef->setFactory([new Reference('ezpublish.solr.boost_factor_provider_factory'), 'buildService']);

        if (isset($config['http_client'])) {
            $this->configureHttpClient($container, $config['http_client']);
        }
    }

    /**
     * Creates needed search services for given connection name and parameters.
     *
     * @param ContainerBuilder $container
     * @param string $connectionName
     * @param array $connectionParams
     */
    private function configureSearchServices(ContainerBuilder $container, $connectionName, $connectionParams)
    {
        $alias = $this->getAlias();

        // Endpoint resolver
        $endpointResolverDefinition = new ChildDefinition(self::ENDPOINT_RESOLVER_ID);
        $endpointResolverDefinition->replaceArgument(0, $connectionParams['entry_endpoints']);
        $endpointResolverDefinition->replaceArgument(1, $connectionParams['mapping']['translations']);
        $endpointResolverDefinition->replaceArgument(2, $connectionParams['mapping']['default']);
        $endpointResolverDefinition->replaceArgument(3, $connectionParams['mapping']['main_translations']);
        $endpointResolverId = "$alias.connection.$connectionName.endpoint_resolver_id";
        $container->setDefinition($endpointResolverId, $endpointResolverDefinition);

        // Core filter
        $coreFilterDefinition = new ChildDefinition(self::CORE_FILTER_ID);
        $coreFilterDefinition->replaceArgument(0, new Reference($endpointResolverId));
        $coreFilterDefinition->addTag('ezpublish.search.solr.core_filter', ['connection' => $connectionName]);
        $coreFilterId = "$alias.connection.$connectionName.core_filter_id";
        $container->setDefinition($coreFilterId, $coreFilterDefinition);

        // Distribution Strategy
        $distributionStrategyId = "$alias.connection.$connectionName.distribution_strategy";

        switch ($connectionParams['distribution_strategy']) {
            case 'standalone':
                $distributionStrategyDefinition = new ChildDefinition(self::STANDALONE_DISTRIBUTION_STRATEGY_ID);
                $distributionStrategyDefinition->setArgument(1, new Reference($endpointResolverId));
                break;
            case 'cloud':
                $distributionStrategyDefinition = new ChildDefinition(self::CLOUD_DISTRIBUTION_STRATEGY_ID);
                $distributionStrategyDefinition->setArgument(1, new Reference($endpointResolverId));
                break;
            default:
                throw new \RuntimeException('Unknown distribution strategy');
        }

        $container->setDefinition($distributionStrategyId, $distributionStrategyDefinition);

        // Gateway
        $gatewayDefinition = new ChildDefinition(self::GATEWAY_ID);
        $gatewayDefinition->replaceArgument('$endpointResolver', new Reference($endpointResolverId));
        $gatewayDefinition->replaceArgument('$distributionStrategy', new Reference($distributionStrategyId));
        $gatewayDefinition->addTag('ezpublish.search.solr.gateway', ['connection' => $connectionName]);

        $gatewayId = "$alias.connection.$connectionName.gateway_id";
        $container->setDefinition($gatewayId, $gatewayDefinition);
    }

    /**
     * Creates boost factor map parameter for a given $connectionName.
     *
     * @param ContainerBuilder $container
     * @param string $connectionName
     * @param array $connectionParams
     */
    private function configureBoostMap(ContainerBuilder $container, $connectionName, $connectionParams)
    {
        $alias = $this->getAlias();
        $boostFactorMap = $this->buildBoostFactorMap($connectionParams['boost_factors']);
        $boostFactorMapId = "{$alias}.connection.{$connectionName}.boost_factor_map_id";

        $container->setParameter($boostFactorMapId, $boostFactorMap);
    }

    /**
     * Creates indexing depth map parameter for a given $connectionName.
     *
     * @param ContainerBuilder $container
     * @param string $connectionName
     * @param array $connectionParams
     */
    private function configureIndexingDepth(ContainerBuilder $container, $connectionName, $connectionParams)
    {
        $alias = $this->getAlias();

        $defaultIndexingDepthId = "{$alias}.connection.{$connectionName}.indexing_depth.default";
        $contentTypeIndexingDepthMapId = "{$alias}.connection.{$connectionName}.indexing_depth.map";

        $container->setParameter($defaultIndexingDepthId, $connectionParams['indexing_depth']['default']);
        $container->setParameter($contentTypeIndexingDepthMapId, $connectionParams['indexing_depth']['content_type']);
    }

    /**
     * Creates Endpoint definition in the service container.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $alias
     * @param array $params
     */
    protected function defineEndpoint(ContainerBuilder $container, $alias, $params)
    {
        $definition = new Definition(self::ENDPOINT_CLASS, [$params]);
        $definition->addTag(self::ENDPOINT_TAG, ['alias' => $alias]);

        $container->setDefinition(
            sprintf($this->getAlias() . '.endpoints.%s', $alias),
            $definition
        );
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }

    /**
     * Builds boost factor map from the given $config.
     *
     * @see \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider::$map
     *
     * @param array $config
     *
     * @return array
     */
    protected function buildBoostFactorMap(array $config)
    {
        $boostFactorMap = [];

        foreach ($config['content_type'] as $typeIdentifier => $factor) {
            $boostFactorMap['content-fields'][$typeIdentifier]['*'] = $factor;
            $boostFactorMap['meta-fields'][$typeIdentifier]['*'] = $factor;
        }

        foreach ($config['field_definition'] as $typeIdentifier => $mapping) {
            foreach ($mapping as $fieldIdentifier => $factor) {
                $boostFactorMap['content-fields'][$typeIdentifier][$fieldIdentifier] = $factor;
            }
        }

        foreach ($config['meta_field'] as $typeIdentifier => $mapping) {
            foreach ($mapping as $fieldIdentifier => $factor) {
                $boostFactorMap['meta-fields'][$typeIdentifier][$fieldIdentifier] = $factor;
            }
        }

        return $boostFactorMap;
    }

    /**
     * @phpstan-param SolrHttpClientConfigArray $httpClientConfig
     */
    private function configureHttpClient(ContainerBuilder $container, array $httpClientConfig): void
    {
        $container->setParameter('ibexa.solr.http_client.timeout', $httpClientConfig['timeout']);
        $container->setParameter(
            'ibexa.solr.http_client.max_retries',
            $httpClientConfig['max_retries']
        );
    }
}
