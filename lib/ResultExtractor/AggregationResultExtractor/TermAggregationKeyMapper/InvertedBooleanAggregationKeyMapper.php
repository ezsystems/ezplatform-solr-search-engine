<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class InvertedBooleanAggregationKeyMapper implements TermAggregationKeyMapper
{
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        return [
            true => false,
            false => true,
        ];
    }
}
