<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Solr\Gateway\UpdateSerializer;

use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\Field;
use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;
use Ibexa\Solr\Gateway\UpdateSerializer\JsonUpdateSerializer;
use PHPUnit\Framework\TestCase;
use eZ\Publish\SPI\Search\FieldType;

/**
 * @covers \Ibexa\Solr\Gateway\UpdateSerializer\JsonUpdateSerializer
 */
class JsonUpdateSerializerTest extends TestCase
{
    public const FIELD_NAME_GENERATOR_MAP = [
        'ez_integer' => 'i',
        'ez_minteger' => 'mi',
        'ez_id' => 'id',
        'ez_mid' => 'mid',
        'ez_string' => 's',
        'ez_mstring' => 'ms',
        'ez_text' => 't',
        'ez_boolean' => 'b',
        'ez_date' => 'dt',
        'ez_point' => 'p',
        'ez_document' => 'doc',
        'ez_fulltext' => 'fulltext',
    ];

    /** @var \Ibexa\Solr\Gateway\UpdateSerializer\JsonUpdateSerializer */
    private $serializer;

    public function getDataForTestSerialize(): iterable
    {
        yield [
            [
                [
                    'id' => 'content1langenggb',
                    'content_id_id' => '1',
                    'content_id_normalized_i' => 1,
                    'content_version_no_i' => 2,
                    'content_name_s' => 'Home',
                    'content_language_codes_ms' => ['eng-GB', 'pol-PL'],
                    'content_language_codes_raw_mid' => ['eng-GB', 'pol-PL'],
                    'content_main_language_code_s' => 'eng-GB',
                    'content_always_available_b' => true,
                    'document_type_id' => 'content',
                    'meta_content__name_t' => ['Home'],
                    'meta_content__text_t' => ['Foo Bar', 'Bar Baz'],
                    '_childDocuments_' => [
                        [
                            'id' => 'content1langenggbnesteddummy',
                            'document_type_id' => 'nesteddummy',
                        ],
                    ],
                ],
            ],
            [
                new Document(
                    [
                        'id' => 'content1langenggb',
                        'languageCode' => 'eng-GB',
                        'alwaysAvailable' => true,
                        'isMainTranslation' => true,
                        'fields' => [
                            new Field('id', 'content1langenggb', new FieldType\IdentifierField()),
                            new Field('content_id', 1, new FieldType\IdentifierField()),
                            new Field(
                                'content_id_normalized', 1, new FieldType\IntegerField()
                            ),
                            new Field(
                                'content_version_no', 2, new FieldType\IntegerField()
                            ),
                            new Field(
                                'content_name',
                                'Home',
                                new FieldType\StringField()
                            ),
                            new Field(
                                'content_language_codes',
                                ['eng-GB', 'pol-PL'],
                                new FieldType\MultipleStringField()
                            ),
                            new Field(
                                'content_language_codes_raw',
                                ['eng-GB', 'pol-PL'],
                                new FieldType\MultipleIdentifierField(['raw' => true])
                            ),
                            new Field(
                                'content_main_language_code',
                                'eng-GB',
                                new FieldType\StringField()
                            ),
                            new Field(
                                'content_always_available', true, new FieldType\BooleanField()
                            ),
                            new Field(
                                'document_type',
                                DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_CONTENT,
                                new FieldType\IdentifierField()
                            ),
                            new Field(
                                'meta_content__name',
                                'Home',
                                new FieldType\TextField()
                            ),
                            new Field(
                                'meta_content__text',
                                'Foo Bar',
                                new FieldType\TextField()
                            ),
                            new Field(
                                'meta_content__text',
                                'Bar Baz',
                                new FieldType\TextField()
                            ),
                        ],
                    ]
                ),
            ],
        ];
    }

    protected function setUp(): void
    {
        $fieldValueMapper = new FieldValueMapper\Aggregate([
            new FieldValueMapper\IdentifierMapper(),
            new FieldValueMapper\MultipleIdentifierMapper(),
            new FieldValueMapper\IntegerMapper(),
            new FieldValueMapper\StringMapper(),
            new FieldValueMapper\MultipleStringMapper(),
            new FieldValueMapper\BooleanMapper(),
        ]);

        $fieldNameGenerator = new FieldNameGenerator(self::FIELD_NAME_GENERATOR_MAP);
        $this->serializer = new JsonUpdateSerializer($fieldValueMapper, $fieldNameGenerator);
    }

    /**
     * @dataProvider getDataForTestSerialize
     *
     * @throws \JsonException
     */
    public function testSerialize(array $expectedData, array $inputDocuments): void
    {
        self::assertSame(
            json_encode($expectedData, JSON_THROW_ON_ERROR),
            $this->serializer->serialize($inputDocuments)
        );
    }

    public function testGetSupportedFormat(): void
    {
        self::assertSame('json', $this->serializer->getSupportedFormat());
    }
}
