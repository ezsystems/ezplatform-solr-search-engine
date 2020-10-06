<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\Factory;

use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver\SearchFieldAggregationFieldResolver;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\RangeAggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\StatsAggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\TermAggregationVisitor;

final class SearchFieldAggregationVisitorFactory
{
    public function createRangeAggregationVisitor(
        string $aggregationClass,
        string $searchIndexFieldName
    ): AggregationVisitor {
        return new RangeAggregationVisitor(
            $aggregationClass,
            new SearchFieldAggregationFieldResolver($searchIndexFieldName)
        );
    }

    public function createStatsAggregationVisitor(
        string $aggregationClass,
        string $searchIndexFieldName
    ): AggregationVisitor {
        return new StatsAggregationVisitor(
            $aggregationClass,
            new SearchFieldAggregationFieldResolver($searchIndexFieldName)
        );
    }

    public function createTermAggregationVisitor(
        string $aggregationClass,
        string $searchIndexFieldName
    ): AggregationVisitor {
        return new TermAggregationVisitor(
            $aggregationClass,
            new SearchFieldAggregationFieldResolver($searchIndexFieldName)
        );
    }
}
