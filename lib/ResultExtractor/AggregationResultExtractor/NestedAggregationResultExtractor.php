<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use stdClass;

final class NestedAggregationResultExtractor implements AggregationResultExtractor
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor */
    private $innerResultExtractor;

    /** @var string */
    private $nestedResultKey;

    public function __construct(AggregationResultExtractor $innerResultExtractor, string $nestedResultKey)
    {
        $this->innerResultExtractor = $innerResultExtractor;
        $this->nestedResultKey = $nestedResultKey;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $this->innerResultExtractor->canVisit($aggregation, $languageFilter);
    }

    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        return $this->innerResultExtractor->extract(
            $aggregation,
            $languageFilter,
            $data->{$this->nestedResultKey} ?? new stdClass()
        );
    }
}
