<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper\NullRangeAggregationKeyMapper;

final class NullRangeAggregationKeyMapperTest extends AbstractRangeAggregationKeyMapperTest
{
    public function dataProviderForTestMap(): iterable
    {
        yield 'null' => [
            $this->createAggregationMock(),
            self::EXAMPLE_LANGUAGE_FILTER,
            '*',
            null,
        ];

        yield 'key' => [
            $this->createAggregationMock(),
            self::EXAMPLE_LANGUAGE_FILTER,
            'foo',
            'foo',
        ];
    }

    protected function createRangeAggregationKeyMapper(): RangeAggregationKeyMapper
    {
        return new NullRangeAggregationKeyMapper();
    }
}
