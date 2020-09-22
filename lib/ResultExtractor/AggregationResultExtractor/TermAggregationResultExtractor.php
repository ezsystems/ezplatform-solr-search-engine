<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\NullAggregationKeyMapper;
use stdClass;

final class TermAggregationResultExtractor implements AggregationResultExtractor
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper */
    private $keyMapper;

    /** @var string */
    private $aggregationClass;

    public function __construct(string $aggregationClass, TermAggregationKeyMapper $keyMapper = null)
    {
        if ($keyMapper === null) {
            $keyMapper = new NullAggregationKeyMapper();
        }

        $this->keyMapper = $keyMapper;
        $this->aggregationClass = $aggregationClass;
    }

    public function canVisit(AggregationInterface $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    public function extract(AggregationInterface $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        $entries = [];

        $mappedKeys = $this->keyMapper->map(
            $aggregation,
            $languageFilter,
            $this->getKeys($data)
        );

        foreach ($data->buckets as $bucket) {
            $key = $bucket->val;

            if (isset($mappedKeys[$key])) {
                $entries[] = new TermAggregationResultEntry(
                    $mappedKeys[$key],
                    $bucket->count
                );
            }
        }

        return new TermAggregationResult($aggregation->getName(), $entries);
    }

    private function getKeys(stdClass $data): array
    {
        $keys = [];
        foreach ($data->buckets as $bucket) {
            $keys[] = $bucket->val;
        }

        return $keys;
    }
}