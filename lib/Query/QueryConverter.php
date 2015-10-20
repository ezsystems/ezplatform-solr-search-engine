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

use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Converts the query tree into an array of Solr query parameters.
 */
abstract class QueryConverter
{
    /**
     * Map query to a proper Solr representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return array
     */
    abstract public function convert(Query $query);
}
