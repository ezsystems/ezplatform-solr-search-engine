<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\RangeAggregationVisitor;

final class RangeAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $aggregationFieldResolver;

    protected function setUp(): void
    {
        $this->aggregationFieldResolver = $this->createMock(AggregationFieldResolver::class);
        $this->aggregationFieldResolver
            ->method('resolveTargetField')
            ->with($this->isInstanceOf(AbstractRangeAggregation::class))
            ->willReturn('custom_field_id');

        parent::setUp();
    }

    protected function createVisitor(): AggregationVisitor
    {
        return new RangeAggregationVisitor(AbstractRangeAggregation::class, $this->aggregationFieldResolver);
    }

    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            $this->createMock(AbstractRangeAggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            true,
        ];

        yield 'false' => [
            $this->createMock(Aggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            false,
        ];
    }

    public function dataProviderForVisit(): iterable
    {
        $aggregation = $this->createMock(AbstractRangeAggregation::class);
        $aggregation->method('getRanges')->willReturn([
            new Range(null, 10),
            new Range(10, 100),
            new Range(100, null),
        ]);

        yield [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'query',
                'q' => '*:*',
                'facet' => [
                    '*_10' => [
                        'type' => 'query',
                        'q' => 'custom_field_id:[* TO 10}',
                    ],
                    '10_100' => [
                        'type' => 'query',
                        'q' => 'custom_field_id:[10 TO 100}',
                    ],
                    '100_*' => [
                        'type' => 'query',
                        'q' => 'custom_field_id:[100 TO *}',
                    ],
                ],
            ],
        ];
    }
}
