<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\AuthorTermAggregation;
use eZ\Publish\Core\FieldType\Author\Author;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\AuthorAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class AuthorAggregationKeyMapperTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestMap
     */
    public function testMap(
        Aggregation $aggregation,
        array $languageFilter,
        array $keys,
        array $expectedResult
    ): void {
        $mapper = new AuthorAggregationKeyMapper();

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
        $input = [
            '{"name":"Boba Fett","email":"boba.fett@example.com"}',
            '{"name":"Leia Organa","email":"leia.organa@example.com"}',
            '{"name":"Luke Skywalker","email":"luke.skywalker@example.com"}',
        ];

        $output = [
            new Author([
                'name' => 'Boba Fett',
                'email' => 'boba.fett@example.com',
            ]),
            new Author([
                'name' => 'Leia Organa',
                'email' => 'leia.organa@example.com',
            ]),
            new Author([
                'name' => 'Luke Skywalker',
                'email' => 'luke.skywalker@example.com',
            ]),
        ];

        yield 'default' => [
            new AuthorTermAggregation('example_aggregation', 'article', 'author'),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            $input,
            array_combine($input, $output),
        ];

        yield 'skip on decode error' => [
            new AuthorTermAggregation('example_aggregation', 'article', 'author'),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            $input + [
                'INVALID_JSON',
            ],
            array_combine($input, $output),
        ];
    }
}
