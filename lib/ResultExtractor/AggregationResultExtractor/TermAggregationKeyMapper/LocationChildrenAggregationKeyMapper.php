<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class LocationChildrenAggregationKeyMapper implements TermAggregationKeyMapper
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\LocationChildrenTermAggregation $aggregation
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $result = [];

        $locations = $this->locationService->loadLocationList(array_map('intval', $keys));
        foreach ($locations as $id => $location) {
            $result["$id"] = $location;
        }

        return $result;
    }
}
