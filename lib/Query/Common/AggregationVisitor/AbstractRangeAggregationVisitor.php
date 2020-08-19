<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use DateTimeInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;

abstract class AbstractRangeAggregationVisitor implements AggregationVisitor
{
    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        AggregationInterface $aggregation,
        array $languageFilter
    ): array {
        $field = $this->getTargetField($aggregation);

        $rangeFacets = [];
        foreach ($aggregation->getRanges() as $range) {
            $from = $this->formatRangeValue($range->getFrom());
            $to = $this->formatRangeValue($range->getTo());

            $rangeFacets["${from}_${to}"] = [
                'type' => 'query',
                'q' => "$field:[$from TO $to]",
            ];
        }

        return [
            'type' => 'query',
            'q' => '*:*',
            'facet' => $rangeFacets,
        ];
    }

    abstract protected function getTargetField(AbstractRangeAggregation $aggregation): string;

    private function formatRangeValue($value)
    {
        if ($value === null) {
            return '*';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d\\TH:i:s\\Z');
        }

        return $value;
    }
}