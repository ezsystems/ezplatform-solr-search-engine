<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Location\CriterionVisitor\Location;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;

/**
 * Visits the Depth criterion.
 */
class DepthIn extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\Location\Depth &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $values = [];
        foreach ($criterion->value as $value) {
            $values[] = 'depth_i:"' . $value . '"';
        }

        return '(' . implode(' OR ', $values) . ')';
    }
}
