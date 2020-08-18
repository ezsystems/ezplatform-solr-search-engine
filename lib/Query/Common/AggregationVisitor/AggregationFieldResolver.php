<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;

/**
 * Resolves search index field name used for aggregation.
 */
interface AggregationFieldResolver
{
    public function resolveTargetField(AggregationInterface $aggregation): string;
}