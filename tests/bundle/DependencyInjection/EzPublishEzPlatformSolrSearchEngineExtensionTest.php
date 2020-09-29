<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngineBundle\Tests\DependencyInjection;

use EzSystems\EzPlatformSolrSearchEngineBundle\DependencyInjection\EzSystemsEzPlatformSolrSearchEngineExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class EzPublishEzPlatformSolrSearchEngineExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngineBundle\DependencyInjection\EzSystemsEzPlatformSolrSearchEngineExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new EzSystemsEzPlatformSolrSearchEngineExtension();

        parent::setUp();
    }

    protected function getContainerExtensions(): array
    {
        return [$this->extension];
    }

    protected function getMinimalConfiguration(): array
    {
        return [];
    }

    public function testEmpty()
    {
        $this->load();
    }

    public function dataProviderForTestEndpoint()
    {
        return [
            [
                'endpoint_dsn',
                [
                    'dsn' => 'https://jura:pura@10.10.10.10:5434/jolr',
                    'core' => 'core0',
                ],
                [
                    'dsn' => 'https://jura:pura@10.10.10.10:5434/jolr',
                    'scheme' => 'http',
                    'host' => '127.0.0.1',
                    'port' => 8983,
                    'user' => null,
                    'pass' => null,
                    'path' => '/solr',
                    'core' => 'core0',
                ],
            ],
            [
                'endpoint_standalone',
                [
                    'scheme' => 'https',
                    'host' => '22.22.22.22',
                    'port' => 1232,
                    'user' => 'jura',
                    'pass' => 'pura',
                    'path' => '/holr',
                    'core' => 'core1',
                ],
                [
                    'dsn' => null,
                    'scheme' => 'https',
                    'host' => '22.22.22.22',
                    'port' => 1232,
                    'user' => 'jura',
                    'pass' => 'pura',
                    'path' => '/holr',
                    'core' => 'core1',
                ],
            ],
            [
                'endpoint_override',
                [
                    'dsn' => 'https://miles:teg@257.258.259.400:5555/noship',
                    'scheme' => 'http',
                    'host' => 'farm.com',
                    'port' => 1234,
                    'core' => 'core2',
                    'user' => 'darwi',
                    'pass' => 'odrade',
                    'path' => '/dunr',
                ],
                [
                    'dsn' => 'https://miles:teg@257.258.259.400:5555/noship',
                    'scheme' => 'http',
                    'host' => 'farm.com',
                    'port' => 1234,
                    'user' => 'darwi',
                    'pass' => 'odrade',
                    'path' => '/dunr',
                    'core' => 'core2',
                ],
            ],
            [
                'endpoint_defaults',
                [
                    'core' => 'core3',
                ],
                [
                    'dsn' => null,
                    'scheme' => 'http',
                    'host' => '127.0.0.1',
                    'port' => 8983,
                    'user' => null,
                    'pass' => null,
                    'path' => '/solr',
                    'core' => 'core3',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestEndpoint
     *
     * @param string $endpointName
     * @param array $endpointValues
     * @param array $expectedArgument
     */
    public function testEndpoint($endpointName, $endpointValues, $expectedArgument)
    {
        $this->load(['endpoints' => [$endpointName => $endpointValues]]);

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            "ez_search_engine_solr.endpoints.{$endpointName}",
            'ezpublish.search.solr.endpoint',
            ['alias' => $endpointName]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            "ez_search_engine_solr.endpoints.{$endpointName}",
            0,
            $expectedArgument
        );
    }

    public function testEndpointCoreRequired()
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->load(
            [
                'endpoints' => [
                    'endpoint0' => [
                        'dsn' => 'https://12.13.14.15:4444/solr',
                    ],
                ],
            ]
        );
    }

    public function dataProviderForTestConnection()
    {
        return [
            [
                [
                    'connections' => [],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'entry_endpoints' => [],
                            'mapping' => [],
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'entry_endpoints' => [],
                            'mapping' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $configurationValues
     *
     * @dataProvider dataProviderForTestConnection
     */
    public function testConnectionLoad($configurationValues)
    {
        $this->load($configurationValues);
    }

    public function testConnection()
    {
        $configurationValues = [
            'connections' => [
                'connection1' => [
                    'entry_endpoints' => [
                        'endpoint1',
                        'endpoint2',
                    ],
                    'mapping' => [
                        'translations' => [
                            'cro-HR' => 'endpoint1',
                            'eng-GB' => 'endpoint2',
                            'gal-MW' => 'endpoint3',
                        ],
                        'default' => 'endpoint4',
                        'main_translations' => 'endpoint5',
                    ],
                ],
            ],
        ];

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            0,
            [
                'endpoint1',
                'endpoint2',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            1,
            [
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
                'gal-MW' => 'endpoint3',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            2,
            'endpoint4'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            3,
            'endpoint5'
        );
        $this->assertContainerBuilderHasService(
            'ez_search_engine_solr.connection.connection1.core_filter_id'
        );
        $this->assertContainerBuilderHasService(
            'ez_search_engine_solr.connection.connection1.gateway_id'
        );
    }

    public function testConnectionEndpointDefaults()
    {
        $configurationValues = [
            'connections' => [
                'connection1' => [
                    'mapping' => [
                        'translations' => [
                            'cro-HR' => 'endpoint1',
                            'eng-GB' => 'endpoint2',
                        ],
                        'default' => 'endpoint3',
                        'main_translations' => 'endpoint4',
                    ],
                ],
            ],
        ];

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            0,
            [
                'endpoint1',
                'endpoint2',
                'endpoint3',
                'endpoint4',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            1,
            [
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            2,
            'endpoint3'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            3,
            'endpoint4'
        );
        $this->assertContainerBuilderHasService(
            'ez_search_engine_solr.connection.connection1.core_filter_id'
        );
        $this->assertContainerBuilderHasService(
            'ez_search_engine_solr.connection.connection1.gateway_id'
        );
    }

    public function testConnectionEndpointUniqueDefaults()
    {
        $configurationValues = [
            'connections' => [
                'connection1' => [
                    'mapping' => [
                        'translations' => [
                            'cro-HR' => 'endpoint1',
                            'eng-GB' => 'endpoint2',
                        ],
                        'default' => 'endpoint2',
                        'main_translations' => 'endpoint2',
                    ],
                ],
            ],
        ];

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            0,
            [
                'endpoint1',
                'endpoint2',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            1,
            [
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            2,
            'endpoint2'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            3,
            'endpoint2'
        );
        $this->assertContainerBuilderHasService(
            'ez_search_engine_solr.connection.connection1.core_filter_id'
        );
        $this->assertContainerBuilderHasService(
            'ez_search_engine_solr.connection.connection1.gateway_id'
        );
    }

    public function testConnectionMappingDefaults()
    {
        $configurationValues = [
            'connections' => [
                'connection1' => [
                    'mapping' => 'endpoint1',
                ],
            ],
        ];

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            0,
            [
                'endpoint1',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            1,
            []
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            2,
            'endpoint1'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ez_search_engine_solr.connection.connection1.endpoint_resolver_id',
            3,
            null
        );
        $this->assertContainerBuilderHasService(
            'ez_search_engine_solr.connection.connection1.core_filter_id'
        );
        $this->assertContainerBuilderHasService(
            'ez_search_engine_solr.connection.connection1.gateway_id'
        );
    }

    public function dataProvideForTestBoostFactorMap()
    {
        return [
            [
                [
                    'connections' => [
                        'connection1' => [],
                    ],
                ],
                [],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [],
                        ],
                    ],
                ],
                [],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'content_type' => [
                                    'article' => 1.5,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 1.5,
                        ],
                    ],
                    'meta-fields' => [
                        'article' => [
                            '*' => 1.5,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'field_definition' => [
                                    'title' => 1.5,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        '*' => [
                            'title' => 1.5,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'field_definition' => [
                                    'title' => 2.2,
                                    'article' => [
                                        'title' => 1.5,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        '*' => [
                            'title' => 2.2,
                        ],
                        'article' => [
                            'title' => 1.5,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'content_type' => [
                                    'article' => 5.5,
                                ],
                                'field_definition' => [
                                    'title' => 2.2,
                                    'article' => [
                                        'title' => 1.5,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 5.5,
                            'title' => 1.5,
                        ],
                        '*' => [
                            'title' => 2.2,
                        ],
                    ],
                    'meta-fields' => [
                        'article' => [
                            '*' => 5.5,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'content_type' => [
                                    'article' => 5.5,
                                ],
                                'meta_field' => [
                                    'text' => 2.2,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 5.5,
                        ],
                    ],
                    'meta-fields' => [
                        'article' => [
                            '*' => 5.5,
                        ],
                        '*' => [
                            'text' => 2.2,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'meta_field' => [
                                    'text' => 2.2,
                                    'article' => [
                                        'name' => 7.8,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'meta-fields' => [
                        '*' => [
                            'text' => 2.2,
                        ],
                        'article' => [
                            'name' => 7.8,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProvideForTestBoostFactorMap
     */
    public function testBoostFactorMap(array $configuration, array $map)
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.connection.connection1.boost_factor_map_id',
            $map
        );
    }
}
