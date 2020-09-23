<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\TermAggregationVisitor;

final class TermAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $aggregationFieldResolver;

    protected function setUp(): void
    {
        $this->aggregationFieldResolver = $this->createMock(AggregationFieldResolver::class);
        $this->aggregationFieldResolver
            ->method('resolveTargetField')
            ->with($this->isInstanceOf(AbstractTermAggregation::class))
            ->willReturn('custom_field_id');

        parent::setUp();
    }

    protected function createVisitor(): AggregationVisitor
    {
        return new TermAggregationVisitor(AbstractTermAggregation::class, $this->aggregationFieldResolver);
    }

    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            $this->createMock(AbstractTermAggregation::class),
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
        $aggregation = $this->createMock(AbstractTermAggregation::class);
        $aggregation->method('getLimit')->willReturn(100);
        $aggregation->method('getMinCount')->willReturn(10);

        yield [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'terms',
                'field' => 'custom_field_id',
                'limit' => 100,
                'mincount' => 10,
            ],
        ];
    }
}