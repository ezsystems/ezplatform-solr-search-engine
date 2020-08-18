<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class SectionAggregationKeyMapper implements TermAggregationKeyMapper
{
    /** @var \eZ\Publish\API\Repository\SectionService */
    private $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\SectionTermAggregation $aggregation
     * @param array $languageFilter
     * @param string[] $keys
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section[]
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            try {
                $result[$key] = $this->sectionService->loadSection((int)$key);
            } catch (NotFoundException | UnauthorizedException $e) {
                // Skip missing section
            }
        }

        return $result;
    }
}
