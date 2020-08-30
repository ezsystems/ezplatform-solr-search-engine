<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use stdClass;

final class RangeAggregationResultExtractor implements AggregationResultExtractor
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
        $entries = [];

        foreach ($data as $key => $bucket) {
            if ($key === 'count') {
                continue;
            }

            list($from, $to) = explode('_', $key);

            $entries[] = new RangeAggregationResultEntry(
                new Range(
                    $this->getRangeBorderFromString($from),
                    $this->getRangeBorderFromString($to),
                ),
                $bucket->count
            );
        }

        return new RangeAggregationResult($aggregation->getName(), $entries);
    }

    private function getRangeBorderFromString(string $from): ?int
    {
        if ($from === '*') {
            return null;
        }

        // TODO: Support from float values
        return (int)$from;
    }
}
