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

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

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
    }

    /**
     * Processes connection configuration by flattening connection parameters
     * and setting them to the container as parameters.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    protected function processConnectionConfiguration(ContainerBuilder $container, $config)
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
            $container->setParameter("$alias.connection.$name", $params);
        }

        foreach ($config['endpoints'] as $name => $params) {
            $this->defineEndpoint($container, $name, $params);
        }

        // Search engine itself, for given connection name
        $searchEngineDef = $container->findDefinition(self::ENGINE_ID);
        $searchEngineDef->setFactory([new Reference('ezpublish.solr.engine_factory'), 'buildEngine']);
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
        $endpointResolverDefinition = new DefinitionDecorator(self::ENDPOINT_RESOLVER_ID);
        $endpointResolverDefinition->replaceArgument(0, $connectionParams['entry_endpoints']);
        $endpointResolverDefinition->replaceArgument(1, $connectionParams['mapping']['translations']);
        $endpointResolverDefinition->replaceArgument(2, $connectionParams['mapping']['default']);
        $endpointResolverDefinition->replaceArgument(3, $connectionParams['mapping']['main_translations']);
        $endpointResolverId = "$alias.connection.$connectionName.endpoint_resolver_id";
        $container->setDefinition($endpointResolverId, $endpointResolverDefinition);

        // Core filter
        $coreFilterDefinition = new DefinitionDecorator(self::CORE_FILTER_ID);
        $coreFilterDefinition->replaceArgument(0, new Reference($endpointResolverId));
        $coreFilterId = "$alias.connection.$connectionName.core_filter_id";
        $container->setDefinition($coreFilterId, $coreFilterDefinition);

        // Gateway
        $gatewayDefinition = new DefinitionDecorator(self::GATEWAY_ID);
        $gatewayDefinition->replaceArgument(1, new Reference($endpointResolverId));
        $gatewayId = "$alias.connection.$connectionName.gateway_id";
        $container->setDefinition($gatewayId, $gatewayDefinition);
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
        $definition = new Definition(self::ENDPOINT_CLASS, array($params));
        $definition->addTag(self::ENDPOINT_TAG, array('alias' => $alias));

        $container->setDefinition(
            sprintf($this->getAlias() . '.endpoints.%s', $alias),
            $definition
        );
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }
}
