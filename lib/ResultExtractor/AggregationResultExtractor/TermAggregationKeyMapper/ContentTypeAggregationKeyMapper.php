<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class ContentTypeAggregationKeyMapper implements TermAggregationKeyMapper
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ContentTypeTermAggregation $aggregation
     * @param array $languageFilter
     * @param string[] $keys
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[]
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $result = [];

        $contentTypes = $this->contentTypeService->loadContentTypeList(array_map('intval', $keys));
        foreach ($contentTypes as $contentType) {
            $result["{$contentType->id}"] = $contentType;
        }

        return $result;
    }
}
