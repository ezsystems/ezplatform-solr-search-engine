<?php

/**
 * File containing the Query Converter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Query;

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
