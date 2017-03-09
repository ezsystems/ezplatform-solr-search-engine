<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    protected $rootNodeName;

    /**
     * Holds default endpoint values.
     *
     * @var array
     */
    protected $defaultEndpointValues = array(
        'scheme' => 'http',
        'host' => '127.0.0.1',
        'port' => 8983,
        'user' => null,
        'pass' => null,
        'path' => '/solr',
    );

    public function __construct($rootNodeName)
    {
        $this->rootNodeName = $rootNodeName;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->rootNodeName);

        $this->addEndpointsSection($rootNode);
        $this->addConnectionsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds endpoints definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function addEndpointsSection(ArrayNodeDefinition $node)
    {
        $node->children()
            ->arrayNode('endpoints')
                ->info('Solr Search Engine endpoint configuration')
                ->useAttributeAsKey('endpoint_name')
                ->performNoDeepMerging()
                ->prototype('array')
                    ->children()
                        // To support Symfony 3 env() variables we don't parse the dsn setting here but in Endpoint ctor
                        ->scalarNode('dsn')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('scheme')
                            ->defaultValue($this->defaultEndpointValues['scheme'])
                        ->end()
                        ->scalarNode('host')
                            ->defaultValue($this->defaultEndpointValues['host'])
                        ->end()
                        ->scalarNode('port')
                            ->defaultValue($this->defaultEndpointValues['port'])
                        ->end()
                        ->scalarNode('user')
                            ->defaultValue($this->defaultEndpointValues['user'])
                        ->end()
                        ->scalarNode('pass')
                            ->defaultValue($this->defaultEndpointValues['pass'])
                        ->end()
                        ->scalarNode('path')
                            ->defaultValue($this->defaultEndpointValues['path'])
                        ->end()
                        ->scalarNode('core')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * Adds connections definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function addConnectionsSection(ArrayNodeDefinition $node)
    {
        $node->children()
            ->scalarNode('default_connection')
                ->info('Name of the default connection')
            ->end()
            ->arrayNode('connections')
            ->info('Solr Search Engine connection configuration')
            ->useAttributeAsKey('connection_name')
            ->performNoDeepMerging()
            ->prototype('array')
                ->beforeNormalization()
                    ->ifTrue(
                        function ($v) {
                            return (
                                !empty($v['mapping']) && !is_array($v['mapping'])
                            );
                        }
                    )
                    ->then(
                        function ($v) {
                            // If single endpoint is set for Content mapping, use it as default
                            // mapping for Content index
                            $v['mapping'] = array(
                                'default' => $v['mapping'],
                            );

                            return $v;
                        }
                    )
                ->end()
                ->beforeNormalization()
                    ->ifTrue(
                        function ($v) {
                            return (
                                empty($v['entry_endpoints']) &&
                                (
                                    !empty($v['mapping']['translations']) ||
                                    !empty($v['mapping']['default']) ||
                                    !empty($v['mapping']['main_translations'])
                                )
                            );
                        }
                    )
                    ->then(
                        // If entry endpoints are not provided use mapping endpoints
                        function ($v) {
                            $endpointSet = array();

                            if (!empty($v['mapping']['translations'])) {
                                $endpointSet = array_flip($v['mapping']['translations']);
                            }

                            if (!empty($v['mapping']['default'])) {
                                $endpointSet[$v['mapping']['default']] = true;
                            }

                            if (!empty($v['mapping']['main_translations'])) {
                                $endpointSet[$v['mapping']['main_translations']] = true;
                            }

                            $v['entry_endpoints'] = array_keys($endpointSet);

                            return $v;
                        }
                    )
                ->end()
                ->children()
                    ->arrayNode('entry_endpoints')
                        ->info(
                            "A set of entry endpoint names.\n\n" .
                            'If not set, mapping endpoints will be used.'
                        )
                        ->example(
                            array(
                                'endpoint1',
                                'endpoint2',
                            )
                        )
                        ->prototype('scalar')
                        ->end()
                    ->end()
                    ->arrayNode('mapping')
                        ->info(
                            'Defines a map of translation language codes and Solr ' .
                            "endpoint names for Content index.\n\n" .
                            'Optionally, you can define default and main translations ' .
                            'endpoints. Default one will be used for a translation if it ' .
                            'is not explicitly mapped, and main translations will be used ' .
                            "for indexing translations in the main languages.\n\n" .
                            'If single endpoint name is given, it will be used as a ' .
                            'shortcut to define the default endpoint.'
                        )
                        ->addDefaultsIfNotSet()
                        ->example(
                            array(
                                array(
                                    'translations' => array(
                                        'cro-HR' => 'endpoint1',
                                        'eng-GB' => 'endpoint2',
                                    ),
                                    'default' => 'endpoint3',
                                    'main_translations' => 'endpoint4',
                                ),
                            )
                        )
                        ->children()
                            ->arrayNode('translations')
                                ->normalizeKeys(false)
                                ->useAttributeAsKey('language_code')
                                    ->info(
                                        'A map of translation language codes and Solr ' .
                                        'endpoint names for Content index.'
                                    )
                                    ->example(
                                        array(
                                            'cro-HR' => 'endpoint1',
                                            'eng-GB' => 'endpoint2',
                                        )
                                    )
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->scalarNode('default')
                                ->defaultNull()
                                ->info(
                                    'Default endpoint will be used for indexing ' .
                                    'documents of a translation that is not explicitly ' .
                                    "mapped.\n\n" .
                                    'This setting is optional.'
                                )
                            ->end()
                            ->scalarNode('main_translations')
                                ->defaultNull()
                                ->info(
                                    'Main translations endpoint will be used to index ' .
                                    "documents of translations in the main languages\n\n" .
                                    'This setting is optional. Use it to reduce the ' .
                                    'number of Solr endpoints that the query is ' .
                                    'distributed to when using always available fallback ' .
                                    'or searching only on the main languages.'
                                )
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
