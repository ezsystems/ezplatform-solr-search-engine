<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;

interface AggregationResultExtractor
{
    public function canVisit(AggregationInterface $aggregation, array $languageFilter): bool;

    public function extract(AggregationInterface $aggregation, array $languageFilter, array $data): AggregationResult;
}