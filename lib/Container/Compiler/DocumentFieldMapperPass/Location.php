<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\DocumentFieldMapperPass;

use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\BaseDocumentFieldMapperPass;

/**
 * Compiler pass for aggregate document field mapper for the Location document.
 */
class Location extends BaseDocumentFieldMapperPass
{
    const AGGREGATE_MAPPER_SERVICE_ID = 'ezpublish.search.solr.document_mapper.plugin.location';
    const AGGREGATE_MAPPER_SERVICE_TAG = self::AGGREGATE_MAPPER_SERVICE_ID;
}
