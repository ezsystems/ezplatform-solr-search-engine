<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Repository\Values\Content\Query\Criterion\PermissionSubtree;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;

/**
 * Visits the Subtree criterion.
 */
class SubtreeIn extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            ($criterion instanceof Criterion\Subtree || $criterion instanceof PermissionSubtree) &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
              $criterion->operator === Operator::EQ);
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
        return '(' .
            implode(
                ' OR ',
                array_map(
                    function ($value) {
                        return 'location_path_string_mid:' . str_replace('/', '\\/', $value) . '*';
                    },
                    $criterion->value
                )
            ) .
            ')';
    }
}
