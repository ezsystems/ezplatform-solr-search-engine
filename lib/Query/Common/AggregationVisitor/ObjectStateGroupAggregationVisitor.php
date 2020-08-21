<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;

final class ObjectStateGroupAggregationVisitor implements AggregationVisitor
{
    public function canVisit(AggregationInterface $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof ObjectStateTermAggregation;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        AggregationInterface $aggregation,
        array $languageFilter
    ): array {
        return [
            'type' => 'terms',
            'field' => 'content_object_state_identifiers_ms',
            'prefix' => $aggregation->getObjectStateGroupIdentifier() . ':',
            'limit' => $aggregation->getLimit(),
            'mincount' => $aggregation->getMinCount(),
        ];
    }
}