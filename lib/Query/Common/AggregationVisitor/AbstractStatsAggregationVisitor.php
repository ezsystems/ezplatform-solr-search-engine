<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;

abstract class AbstractStatsAggregationVisitor implements AggregationVisitor
{
    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        AggregationInterface $aggregation,
        array $languageFilter
    ): array {
        $field = $this->getTargetField($aggregation);

        return [
            'type' => 'query',
            'q' => '*:*',
            'facet' => [
                'sum' => "sum($field)",
                'min' => "min($field)",
                'max' => "max($field)",
                'avg' => "avg($field)",
            ]
        ];
    }

    abstract protected function getTargetField(AbstractStatsAggregation $aggregation): string;
}