<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;

final class TermAggregationResultExtractor implements AggregationResultExtractor
{
    /** @var string */
    private $aggregationClass;



    public function canVisit(AggregationInterface $aggregation, array $languageFilter): bool
    {
        // TODO: Implement canVisit() method.
    }

    public function extract(AggregationInterface $aggregation, array $languageFilter, array $data): AggregationResult
    {
        // TODO: Implement extract() method.
    }
}