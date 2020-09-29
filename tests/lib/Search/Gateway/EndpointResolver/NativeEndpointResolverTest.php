<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway\EndpointResolver;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver\NativeEndpointResolver;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\SingleEndpointResolver;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase;
use RuntimeException;

/**
 * Test case for native endpoint resolver.
 */
class NativeEndpointResolverTest extends TestCase
{
    public function testGetEntryEndpoint()
    {
        $entryEndpoints = [
            'endpoint2',
            'endpoint0',
            'endpoint1',
        ];

        $endpointResolver = $this->getEndpointResolver($entryEndpoints);

        $this->assertEquals(
            'endpoint2',
            $endpointResolver->getEntryEndpoint()
        );
    }

    public function testGetEntryEndpointThrowsRuntimeException()
    {
        $this->expectException(RuntimeException::class);
        $entryEndpoints = [];

        $endpointResolver = $this->getEndpointResolver($entryEndpoints);

        $endpointResolver->getEntryEndpoint();
    }

    public function testGetIndexingTarget()
    {
        $endpointMap = [
            'eng-GB' => 'endpoint3',
        ];

        $endpointResolver = $this->getEndpointResolver([], $endpointMap);

        $this->assertEquals(
            'endpoint3',
            $endpointResolver->getIndexingTarget('eng-GB')
        );
    }

    public function testGetIndexingTargetReturnsDefaultEndpoint()
    {
        $endpointMap = [];
        $defaultEndpoint = 'endpoint4';

        $endpointResolver = $this->getEndpointResolver([], $endpointMap, $defaultEndpoint);

        $this->assertEquals(
            'endpoint4',
            $endpointResolver->getIndexingTarget('ger-DE')
        );
    }

    public function getIndexingTargetThrowsRuntimeException()
    {
        $endpointResolver = $this->getEndpointResolver();

        $endpointResolver->getIndexingTarget('ger-DE');
    }

    public function testGetMainLanguagesEndpoint()
    {
        $mainLanguagesEndpoint = 'endpoint5';

        $endpointResolver = $this->getEndpointResolver([], [], null, $mainLanguagesEndpoint);

        $this->assertEquals(
            'endpoint5',
            $endpointResolver->getMainLanguagesEndpoint()
        );
    }

    public function testGetMainLanguagesEndpointReturnsNull()
    {
        $endpointResolver = $this->getEndpointResolver();

        $this->assertNull($endpointResolver->getMainLanguagesEndpoint());
    }

    public function providerForTestGetSearchTargets()
    {
        return [
            // Will return all endpoints (for always available fallback without main languages endpoint)
            0 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            1 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            2 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                ],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                ],
                false,
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            3 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            4 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages + main languages endpoint
            5 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages + main languages endpoint
            6 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages + main languages endpoint
            7 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages + main languages endpoint
            8 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'main_languages_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages
            9 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            10 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                ],
            ],
            // Will return mapped endpoints matched by languages
            11 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            12 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                ],
            ],
            // Will return mapped endpoints matched by languages
            13 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            14 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            15 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            16 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            17 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            18 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            19 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            20 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages only)
            21 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages only)
            22 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages only)
            23 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages only)
            24 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            25 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            26 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            27 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            28 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            29 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            30 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            31 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            32 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            33 => [
                [],
                'default_endpoint',
                null,
                [],
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                [
                    'default_endpoint',
                ],
                false,
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            34 => [
                [],
                null,
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
                false,
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            35 => [
                [],
                'default_endpoint',
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            36 => [
                [],
                'default_endpoint',
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'default_endpoint',
                ],
                false,
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            37 => [
                [],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            38 => [
                [],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'main_languages_endpoint',
                ],
                false,
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            39 => [
                [],
                'default_endpoint',
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                [
                    'default_endpoint',
                ],
                false,
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            40 => [
                [],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            41 => [
                [],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'main_languages_endpoint',
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetSearchTargets
     *
     * @param string[] $endpointMap
     * @param string|null $defaultEndpoint
     * @param string|null $mainLanguagesEndpoint
     * @param array $languageSettings
     * @param string[] $expected
     * @param bool $expectedIsMultiple
     */
    public function testGetSearchTargets(
        $endpointMap,
        $defaultEndpoint,
        $mainLanguagesEndpoint,
        $languageSettings,
        $expected,
        $expectedIsMultiple = true
    ) {
        $endpointResolver = $this->getEndpointResolver(
            [],
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        $actual = $endpointResolver->getSearchTargets($languageSettings);

        $this->assertEquals($expected, $actual);

        if ($endpointResolver instanceof SingleEndpointResolver) {
            $this->assertEquals($expectedIsMultiple, $endpointResolver->hasMultipleEndpoints());
        }
    }

    public function providerForTestGetSearchTargetsThrowsRuntimeException()
    {
        return [
            // Will try to return all endpoints
            0 => [
                [],
                null,
                null,
                [],
                'No endpoints defined',
            ],
            1 => [
                [],
                null,
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                'No endpoints defined',
            ],
            2 => [
                [],
                null,
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                'No endpoints defined',
            ],
            3 => [
                [],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                'No endpoints defined',
            ],
            // Will try to map translation
            4 => [
                [],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ],
            5 => [
                [],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ],
            6 => [
                [],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetSearchTargetsThrowsRuntimeException
     *
     * @param string[] $endpointMap
     * @param string|null $defaultEndpoint
     * @param string|null $mainLanguagesEndpoint
     * @param array $languageSettings
     * @param string $message
     */
    public function testGetSearchTargetsThrowsRuntimeException(
        $endpointMap,
        $defaultEndpoint,
        $mainLanguagesEndpoint,
        $languageSettings,
        $message
    ) {
        $this->expectException(RuntimeException::class);

        $endpointResolver = $this->getEndpointResolver(
            [],
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        try {
            $endpointResolver->getSearchTargets($languageSettings);
        } catch (RuntimeException $e) {
            $this->assertEquals($message, $e->getMessage());

            throw $e;
        }
    }

    public function providerForTestGetEndpoints()
    {
        return [
            [
                [
                    'eng-GB' => 'endpoint_en_GB',
                ],
                null,
                null,
                [
                    'endpoint_en_GB',
                ],
            ],
            [
                [
                    'eng-GB' => 'endpoint_en_GB',
                ],
                'default_endpoint',
                null,
                [
                    'endpoint_en_GB',
                    'default_endpoint',
                ],
            ],
            [
                [
                    'eng-GB' => 'endpoint_en_GB',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'endpoint_en_GB',
                    'default_endpoint',
                    'main_languages_endpoint',
                ],
            ],
            [
                [],
                'default_endpoint',
                null,
                [
                    'default_endpoint',
                ],
            ],
            [
                [],
                null,
                'main_languages_endpoint',
                [
                    'main_languages_endpoint',
                ],
            ],
            [
                [],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'default_endpoint',
                    'main_languages_endpoint',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetEndpoints
     *
     * @param string[] $endpointMap
     * @param string|null $defaultEndpoint
     * @param string|null $mainLanguagesEndpoint
     * @param string[] $expected
     */
    public function testGetEndpoints(
        $endpointMap,
        $defaultEndpoint,
        $mainLanguagesEndpoint,
        $expected
    ) {
        $endpointResolver = $this->getEndpointResolver(
            [],
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        $endpoints = $endpointResolver->getEndpoints();

        $this->assertEquals($expected, $endpoints);
    }

    public function testGetEndpointsThrowsRuntimeException()
    {
        $this->expectException(RuntimeException::class);
        $endpointResolver = $this->getEndpointResolver(
            [],
            [],
            null,
            null
        );

        $endpointResolver->getEndpoints();
    }

    protected function getEndpointResolver(
        array $entryEndpoints = [],
        array $endpointMap = [],
        $defaultEndpoint = null,
        $mainLanguagesEndpoint = null
    ) {
        return new NativeEndpointResolver(
            $entryEndpoints,
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );
    }
}
