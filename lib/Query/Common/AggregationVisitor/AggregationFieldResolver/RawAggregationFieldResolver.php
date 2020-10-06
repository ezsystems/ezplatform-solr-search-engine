<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;

final class RawAggregationFieldResolver implements AggregationFieldResolver
{
    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\RawAggregation $aggregation
     */
    public function resolveTargetField(Aggregation $aggregation): string
    {
        return $aggregation->getFieldName();
    }
}
