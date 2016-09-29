<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\FieldMapperPass;

use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\BaseFieldMapperPass;

/**
 * Compiler pass for aggregate document field mapper for the Location document.
 */
class LocationFieldMapperPass extends BaseFieldMapperPass
{
    const AGGREGATE_MAPPER_SERVICE_ID = 'ezpublish.search.solr.field_mapper.location';
    const AGGREGATE_MAPPER_SERVICE_TAG = self::AGGREGATE_MAPPER_SERVICE_ID;
}
