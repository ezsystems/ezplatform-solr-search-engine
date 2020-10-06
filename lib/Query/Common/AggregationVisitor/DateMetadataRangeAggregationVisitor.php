<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\DateMetadataRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use RuntimeException;

final class DateMetadataRangeAggregationVisitor extends AbstractRangeAggregationVisitor
{
    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof DateMetadataRangeAggregation;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\DateMetadataRangeAggregation $aggregation
     */
    protected function getTargetField(AbstractRangeAggregation $aggregation): string
    {
        switch ($aggregation->getType()) {
            case DateMetadataRangeAggregation::PUBLISHED:
                return 'content_publication_date_dt';
            case DateMetadataRangeAggregation::MODIFIED:
                return 'content_modification_date_dt';
            default:
                throw new RuntimeException("Unsupported DateMetadataRangeAggregation type {$aggregation->getType()}");
        }
    }
}
