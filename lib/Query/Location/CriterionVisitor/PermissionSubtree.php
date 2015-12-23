<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Location\CriterionVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Repository\Values\Content\Query\Criterion as CoreCriterion;

/**
 * Visits the PermissionSubtree criterion.
 */
class PermissionSubtree extends CriterionVisitor
{
    /**
     * {@inheritdoc}
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof CoreCriterion\PermissionSubtree;
    }

    /**
     * {@inheritdoc}
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $subtrees = $criterion->value;
        return 'path_string_id:['.implode('* ', $subtrees).'*]';
    }
}
