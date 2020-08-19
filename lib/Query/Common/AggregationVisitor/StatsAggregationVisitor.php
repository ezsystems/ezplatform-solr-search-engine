<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;

final class StatsAggregationVisitor extends AbstractStatsAggregationVisitor
{
    /** @var string */
    private $aggregationClass;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver */
    private $aggregationFieldResolver;

    public function __construct(string $aggregationClass, AggregationFieldResolver $aggregationFieldResolver)
    {
        $this->aggregationClass = $aggregationClass;
        $this->aggregationFieldResolver = $aggregationFieldResolver;
    }

    public function canVisit(AggregationInterface $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    protected function getTargetField(AbstractStatsAggregation $aggregation): string
    {
        return $this->aggregationFieldResolver->resolveTargetField($aggregation);
    }
}