<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

use DateTimeImmutable;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper\DateTimeRangeAggregationKeyMapper;

final class DateTimeRangeAggregationKeyMapperTest extends AbstractRangeAggregationKeyMapperTest
{
    public function dataProviderForTestMap(): iterable
    {
        yield 'null' => [
            $this->createAggregationMock(),
            self::EXAMPLE_LANGUAGE_FILTER,
            '*',
            null,
        ];

        yield 'date string' => [
            $this->createAggregationMock(),
            self::EXAMPLE_LANGUAGE_FILTER,
            '2020-01-01T00:00:00Z',
            new DateTimeImmutable('2020-01-01T00:00:00Z'),
        ];
    }

    protected function createRangeAggregationKeyMapper(): RangeAggregationKeyMapper
    {
        return new DateTimeRangeAggregationKeyMapper();
    }
}
