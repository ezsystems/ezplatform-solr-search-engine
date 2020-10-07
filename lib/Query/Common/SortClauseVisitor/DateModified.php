<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\SortClauseVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;

/**
 * Visits the sortClause tree into a Solr query.
 */
class DateModified extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\DateModified;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
    {
        return 'content_modification_date_dt' . $this->getDirection($sortClause);
    }
}
