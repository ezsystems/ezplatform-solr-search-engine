<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class ObjectStateAggregationKeyMapper implements TermAggregationKeyMapper
{
    /** @var \eZ\Publish\API\Repository\ObjectStateService */
    private $objectStateService;

    public function __construct(ObjectStateService $objectStateService)
    {
        $this->objectStateService = $objectStateService;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation $aggregation
     */
    public function map(AggregationInterface $aggregation, array $languageFilter, array $keys): array
    {
        $objectStateGroup = $this->objectStateService->loadObjectStateGroupByIdentifier(
            $aggregation->getObjectStateGroupIdentifier()
        );

        $mapped = [];
        foreach ($keys as $key) {
            try {
                $mapped[$key] = $this->objectStateService->loadObjectStateByIdentifier($objectStateGroup, $key);
            } catch (NotFoundException $e) {
                // Skip non-existing object states
            }
        }

        return $mapped;
    }
}