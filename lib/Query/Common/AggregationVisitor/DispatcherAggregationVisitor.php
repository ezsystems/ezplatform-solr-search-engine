<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;

final class DispatcherAggregationVisitor implements AggregationVisitor
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor[] */
    private $visitors;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor[]
     */
    public function __construct(iterable $visitors)
    {
        $this->visitors = $visitors;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $this->findVisitor($aggregation, $languageFilter) !== null;
    }

    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array {
        $visitor = $this->findVisitor($aggregation, $languageFilter);

        if ($visitor === null) {
            throw new NotImplementedException(
                'No visitor available for: ' . get_class($aggregation)
            );
        }

        return $visitor->visit($this, $aggregation, $languageFilter);
    }

    private function findVisitor(Aggregation $aggregation, array $languageFilter): ?AggregationVisitor
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($aggregation, $languageFilter)) {
                return $visitor;
            }
        }

        return null;
    }
}
