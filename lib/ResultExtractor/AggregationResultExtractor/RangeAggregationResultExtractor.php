<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use stdClass;

final class RangeAggregationResultExtractor implements AggregationResultExtractor
{
    /** @var string */
    private $aggregationClass;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper */
    private $keyMapper;

    public function __construct(string $aggregationClass, RangeAggregationKeyMapper $keyMapper)
    {
        $this->aggregationClass = $aggregationClass;
        $this->keyMapper = $keyMapper;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        $entries = [];

        foreach ($data as $key => $bucket) {
            if ($key === 'count') {
                continue;
            }

            if (strpos($key, '_') === false) {
                continue;
            }

            list($from, $to) = explode('_', $key, 2);

            $entries[] = new RangeAggregationResultEntry(
                new Range(
                    $this->keyMapper->map($aggregation, $languageFilter, $from),
                    $this->keyMapper->map($aggregation, $languageFilter, $to),
                ),
                $bucket->count
            );
        }

        return new RangeAggregationResult($aggregation->getName(), $entries);
    }
}
