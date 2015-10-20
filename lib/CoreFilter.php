<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine;

use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Core filter applies conditions on a query object ensuring matching of correct
 * document across multiple Solr indexes.
 */
abstract class CoreFilter
{
    /**
     * Applies conditions on the $query using given $languageSettings.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $languageSettings
     * @param string $documentTypeIdentifier
     */
    abstract public function apply(Query $query, array $languageSettings, $documentTypeIdentifier);
}
