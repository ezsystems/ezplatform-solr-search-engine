<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\Factory;

use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver\RawAggregationFieldResolver;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\RangeAggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\StatsAggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\TermAggregationVisitor;

final class RawAggregationVisitorFactory
{
    public function createRangeAggregationVisitor(
        string $aggregationClass
    ): AggregationVisitor {
        return new RangeAggregationVisitor(
            $aggregationClass,
            new RawAggregationFieldResolver()
        );
    }

    public function createStatsAggregationVisitor(
        string $aggregationClass
    ): AggregationVisitor {
        return new StatsAggregationVisitor(
            $aggregationClass,
            new RawAggregationFieldResolver()
        );
    }

    public function createTermAggregationVisitor(
        string $aggregationClass
    ): AggregationVisitor {
        return new TermAggregationVisitor(
            $aggregationClass,
            new RawAggregationFieldResolver()
        );
    }
}
