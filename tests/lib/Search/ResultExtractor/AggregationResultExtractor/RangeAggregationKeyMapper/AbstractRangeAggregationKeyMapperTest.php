<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;
use PHPUnit\Framework\TestCase;

abstract class AbstractRangeAggregationKeyMapperTest extends TestCase
{
    protected const EXAMPLE_LANGUAGE_FILTER = [];

    /**
     * @dataProvider dataProviderForTestMap
     */
    final public function testMap(Aggregation $aggregation, array $languageFilter, string $key, $expectedResult): void
    {
        $mapper = $this->createRangeAggregationKeyMapper();

        $this->assertEquals(
            $expectedResult,
            $mapper->map($aggregation, $languageFilter, $key)
        );
    }

    abstract public function dataProviderForTestMap(): iterable;

    abstract protected function createRangeAggregationKeyMapper(): RangeAggregationKeyMapper;

    protected function createAggregationMock(): Aggregation
    {
        return $this->createMock(Aggregation::class);
    }
}
