<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\FieldMapper;

use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as SPIFieldDefinition;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase;

/**
 * Test case for the boost factor provider.
 */
class BoostFactorProviderTest extends TestCase
{
    public function providerForTestGetContentFieldBoostFactor()
    {
        return [
            [
                [
                    'content-fields' => [
                        'article' => [
                            'title' => 5.5,
                        ],
                    ],
                ],
                'article',
                'title',
                5.5,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            'title' => 5.5,
                        ],
                    ],
                ],
                'blog_post',
                'title',
                1.0,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'title' => 5.5,
                        ],
                    ],
                ],
                'article',
                'name',
                3.3,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'title' => 5.5,
                        ],
                        '*' => [
                            'name' => 2.2,
                        ],
                    ],
                ],
                'news',
                'name',
                2.2,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'title' => 5.5,
                        ],
                        '*' => [
                            'name' => 2.2,
                        ],
                    ],
                ],
                'news',
                'title',
                1.0,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 3.3,
                        ],
                        '*' => [
                            'name' => 2.2,
                        ],
                    ],
                ],
                'article',
                'name',
                3.3,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetContentFieldBoostFactor
     *
     * @param array $map
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param float $expectedBoostFactor
     */
    public function testGetContentFieldBoostFactor(
        array $map,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier,
        $expectedBoostFactor
    ) {
        $provider = $this->getFieldBoostProvider($map);

        $boostFactor = $provider->getContentFieldBoostFactor(
            $this->getContentTypeStub($contentTypeIdentifier),
            $this->getFieldDefinitionStub($fieldDefinitionIdentifier)
        );

        $this->assertEquals($expectedBoostFactor, $boostFactor);
    }

    public function providerForTestGetContentMetaFieldBoostFactor()
    {
        return [
            [
                [
                    'meta-fields' => [
                        'article' => [
                            'name' => 5.5,
                        ],
                    ],
                ],
                'article',
                'name',
                5.5,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            'name' => 5.5,
                        ],
                    ],
                ],
                'article',
                'text',
                1.0,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'text' => 5.5,
                        ],
                    ],
                ],
                'article',
                'name',
                3.3,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'text' => 5.5,
                        ],
                    ],
                ],
                'blog_post',
                'name',
                1.0,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'name' => 5.5,
                        ],
                        '*' => [
                            'text' => 2.2,
                        ],
                    ],
                ],
                'news',
                'text',
                2.2,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'name' => 5.5,
                        ],
                        '*' => [
                            'text' => 2.2,
                        ],
                    ],
                ],
                'news',
                'name',
                1.0,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                        ],
                        '*' => [
                            'text' => 2.2,
                        ],
                    ],
                ],
                'article',
                'text',
                3.3,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetContentMetaFieldBoostFactor
     *
     * @param array $map
     * @param string $contentTypeIdentifier
     * @param string $fieldName
     * @param float $expectedBoostFactor
     */
    public function testGetContentMetaFieldBoostFactor(
        array $map,
        $contentTypeIdentifier,
        $fieldName,
        $expectedBoostFactor
    ) {
        $provider = $this->getFieldBoostProvider($map);

        $boostFactor = $provider->getContentMetaFieldBoostFactor(
            $this->getContentTypeStub($contentTypeIdentifier),
            $fieldName
        );

        $this->assertEquals($expectedBoostFactor, $boostFactor);
    }

    protected function getFieldBoostProvider(array $map)
    {
        return new BoostFactorProvider($map);
    }

    protected function getContentTypeStub($identifier)
    {
        return new SPIContentType(
            [
                'identifier' => $identifier,
            ]
        );
    }

    protected function getFieldDefinitionStub($identifier)
    {
        return new SPIFieldDefinition(
            [
                'identifier' => $identifier,
            ]
        );
    }
}
