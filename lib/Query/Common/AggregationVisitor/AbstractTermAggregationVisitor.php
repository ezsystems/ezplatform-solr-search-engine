<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;

abstract class AbstractTermAggregationVisitor implements AggregationVisitor
{
    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        AggregationInterface $aggregation,
        array $languageFilter
    ): array {
        return [
            'type' => 'terms',
            'field' => $this->getTargetField($aggregation),
            'limit' => $aggregation->getLimit(),
            'mincount' => $aggregation->getMinCount(),
        ];
    }

    abstract protected function getTargetField(AbstractTermAggregation $aggregation): string;
}