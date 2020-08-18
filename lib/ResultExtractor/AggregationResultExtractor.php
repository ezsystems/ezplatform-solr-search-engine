<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use stdClass;

interface AggregationResultExtractor
{
    public function canVisit(Aggregation $aggregation, array $languageFilter): bool;

    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult;
}
