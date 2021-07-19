<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\LocationChildrenTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;

final class LocationChildrenTermAggregationVisitor implements AggregationVisitor
{
    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof LocationChildrenTermAggregation;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\LocationChildrenTermAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array {
        return [
            'type' => 'terms',
            'field' => 'parent_id_id',
            'prefix' => '',
            'limit' => $aggregation->getLimit(),
            'mincount' => $aggregation->getMinCount(),
        ];
    }
}
