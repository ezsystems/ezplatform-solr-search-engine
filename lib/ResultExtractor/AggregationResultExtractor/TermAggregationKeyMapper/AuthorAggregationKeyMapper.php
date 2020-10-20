<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\Core\FieldType\Author\Author;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class AuthorAggregationKeyMapper implements TermAggregationKeyMapper
{
    /**
     * @return \eZ\Publish\Core\FieldType\Author\Author[]
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            $properties = json_decode($key, true);
            if ($properties !== false) {
                $results[$key] = new Author($properties);
            }
        }

        return $results;
    }
}
