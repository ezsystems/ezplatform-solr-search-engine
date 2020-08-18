<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;

interface AggregationVisitor
{
    /**
     * Check if visitor is applicable to current aggreagtion.
     */
    public function canVisit(Aggregation $aggregation, array $languageFilter): bool;

    /**
     * @return string[]
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array;
}
