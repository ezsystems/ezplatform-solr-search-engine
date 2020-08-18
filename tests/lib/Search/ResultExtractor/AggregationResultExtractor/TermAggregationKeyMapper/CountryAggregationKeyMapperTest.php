<?php

declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\CountryAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class CountryAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_RAW_KEYS = [93, 94, 55];

    /**
     * Example country info entries from ezpublish.fieldType.ezcountry.data parameter.
     */
    private const EXAMPLE_COUNTRIES_INFO = [
        'AF' => [
            'Name' => 'Afghanistan',
            'Alpha2' => 'AF',
            'Alpha3' => 'AFG',
            'IDC' => '93',
        ],
        'AR' => [
            'Name' => 'Argentina',
            'Alpha2' => 'AR',
            'Alpha3' => 'ARG',
            'IDC' => '94',
        ],
        'BR' => [
            'Name' => 'Brazil',
            'Alpha2' => 'BR',
            'Alpha3' => 'BRA',
            'IDC' => '55',
        ],
    ];

    /**
     * @dataProvider dataProviderForTestMap
     */
    public function testMap(
        Aggregation $aggregation,
        array $languageFilter,
        array $keys,
        array $expectedResult
    ): void {
        $mapper = new CountryAggregationKeyMapper(self::EXAMPLE_COUNTRIES_INFO);

        $this->assertEquals(
            $expectedResult,
            $mapper->map(
                $aggregation,
                $languageFilter,
                $keys
            )
        );
    }

    public function dataProviderForTestMap(): iterable
    {
        yield 'default' => [
            new CountryTermAggregation('aggregation', 'product', 'country'),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => 'AFG',
                94 => 'ARG',
                55 => 'BRA',
            ],
        ];

        yield 'alpha2' => [
            new CountryTermAggregation('aggregation', 'product', 'country', CountryTermAggregation::TYPE_ALPHA_2),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => 'AF',
                94 => 'AR',
                55 => 'BR',
            ],
        ];

        yield 'alpha3' => [
            new CountryTermAggregation('aggregation', 'product', 'country', CountryTermAggregation::TYPE_ALPHA_3),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => 'AFG',
                94 => 'ARG',
                55 => 'BRA',
            ],
        ];

        yield 'name' => [
            new CountryTermAggregation('aggregation', 'product', 'country', CountryTermAggregation::TYPE_NAME),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => 'Afghanistan',
                94 => 'Argentina',
                55 => 'Brazil',
            ],
        ];

        yield 'idc' => [
            new CountryTermAggregation('aggregation', 'product', 'country', CountryTermAggregation::TYPE_IDC),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => '93',
                94 => '94',
                55 => '55',
            ],
        ];
    }
}
