<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the sort clause into a Solr query.
 */
abstract class SortClauseVisitor
{
    /**
     * CHeck if visitor is applicable to current sort clause.
     *
     * @return bool
     */
    abstract public function canVisit(SortClause $sortClause);

    /**
     * Map field value to a proper Solr representation.
     *
     * @return string
     */
    abstract public function visit(SortClause $sortClause);

    /**
     * Get solr sort direction for sort clause.
     *
     * @return string
     */
    protected function getDirection(SortClause $sortClause)
    {
        return ' ' . ($sortClause->direction === 'descending' ? 'desc' : 'asc');
    }
}
