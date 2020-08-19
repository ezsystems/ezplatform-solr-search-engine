<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\StatsAggregationResult;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use stdClass;

final class StatsAggregationResultExtractor implements AggregationResultExtractor
{
    /** @var string */
    private $aggregationClass;

    public function __construct(string $aggregationClass)
    {
        $this->aggregationClass = $aggregationClass;
    }

    public function canVisit(AggregationInterface $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    public function extract(AggregationInterface $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        return new StatsAggregationResult(
            $aggregation->getName(),
            $data->count,
            $data->min,
            $data->max,
            $data->avg,
            $data->sum,
        );
    }
}