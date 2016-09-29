<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\DocumentFieldMapperPass;

use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\BaseDocumentFieldMapperPass;

/**
 * Compiler pass for aggregate document field mapper for the Content document
 * in a specific translation.
 */
class ContentTranslation extends BaseDocumentFieldMapperPass
{
    const AGGREGATE_MAPPER_SERVICE_ID = 'ezpublish.search.solr.document_mapper.plugin.content_translation';
    const AGGREGATE_MAPPER_SERVICE_TAG = self::AGGREGATE_MAPPER_SERVICE_ID;
}
