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

/**
 * Visits the LogicalNot criterion.
 */
class LogicalNot extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\LogicalNot;
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
        if (!isset($criterion->criteria[0]) ||
             (\count($criterion->criteria) > 1)) {
            throw new \RuntimeException('Invalid aggregation in LogicalNot criterion.');
        }

        return '(*:* NOT ' . $subVisitor->visit($criterion->criteria[0]) . ')';
    }
}
