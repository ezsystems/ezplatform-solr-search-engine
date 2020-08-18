<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\NullAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class NullAggregationKeyMapperTest extends TestCase
{
    public function testMap(): void
    {
        $mapper = new NullAggregationKeyMapper();

        $this->assertEquals(
            [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ],
            $mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                ['foo', 'bar', 'baz']
            )
        );
    }
}
