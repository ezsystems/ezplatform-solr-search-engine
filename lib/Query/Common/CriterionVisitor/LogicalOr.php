<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use RuntimeException;

/**
 * Visits the LogicalOr criterion.
 */
class LogicalOr extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\LogicalOr;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd $criterion */
        if (!isset($criterion->criteria[0])) {
            throw new RuntimeException('Invalid aggregation in LogicalOr criterion.');
        }

        $subCriteria = array_map(
            function ($value) use ($subVisitor) {
                return $subVisitor->visit($value);
            },
            $criterion->criteria
        );

        if (\count($subCriteria) === 1) {
            return reset($subCriteria);
        }

        return '(' . implode(' OR ', $subCriteria) . ')';
    }
}
