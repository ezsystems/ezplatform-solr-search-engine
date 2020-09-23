<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\ObjectStateAggregationVisitor;

final class ObjectStateGroupAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    protected function createVisitor(): AggregationVisitor
    {
        return new ObjectStateAggregationVisitor();
    }

    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            new ObjectStateTermAggregation('foo', 'ez_lock'),
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
        yield 'defaults' => [
            new ObjectStateTermAggregation('foo', 'ez_lock'),
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'terms',
                'field' => 'content_object_state_identifiers_ms',
                'prefix' => 'ez_lock:',
                'limit' => ObjectStateTermAggregation::DEFAULT_LIMIT,
                'mincount' => ObjectStateTermAggregation::DEFAULT_MIN_COUNT,
            ]
        ];

        $aggregation = new ObjectStateTermAggregation('foo', 'ez_lock');
        $aggregation->setLimit(100);
        $aggregation->setMinCount(10);

        yield 'custom' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'terms',
                'field' => 'content_object_state_identifiers_ms',
                'prefix' => 'ez_lock:',
                'limit' => 100,
                'mincount' => 10,
            ]
        ];
    }
}