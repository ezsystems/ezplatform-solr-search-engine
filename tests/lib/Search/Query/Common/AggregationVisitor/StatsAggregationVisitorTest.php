<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\StatsAggregationVisitor;

final class StatsAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $aggregationFieldResolver;

    protected function setUp(): void
    {
        $this->aggregationFieldResolver = $this->createMock(AggregationFieldResolver::class);
        $this->aggregationFieldResolver
            ->method('resolveTargetField')
            ->with($this->isInstanceOf(AbstractStatsAggregation::class))
            ->willReturn('custom_field_id');

        parent::setUp();
    }

    protected function createVisitor(): AggregationVisitor
    {
        return new StatsAggregationVisitor(AbstractStatsAggregation::class, $this->aggregationFieldResolver);
    }

    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            $this->createMock(AbstractStatsAggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            true,
        ];

        yield 'false' => [
            $this->createMock(AggregationInterface::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            false,
        ];
    }

    public function dataProviderForVisit(): iterable
    {
        yield [
            $this->createMock(AbstractStatsAggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'query',
                'q' => '*:*',
                'facet' => [
                    'sum' => "sum(custom_field_id)",
                    'min' => "min(custom_field_id)",
                    'max' => "max(custom_field_id)",
                    'avg' => "avg(custom_field_id)",
                ]
            ],
        ];
    }
}