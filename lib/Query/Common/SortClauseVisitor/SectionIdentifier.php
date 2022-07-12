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
class SectionIdentifier extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\SectionIdentifier;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
    {
        return 'content_section_identifier_s' . $this->getDirection($sortClause);
    }
}
