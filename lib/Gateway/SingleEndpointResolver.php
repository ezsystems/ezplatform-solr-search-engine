<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway;

/**
 * Additional interface for Endpoint resolvers which resolves Solr backend endpoints.
 */
interface SingleEndpointResolver
{
    /**
     * Returns true if current configurations has several endpoints.
     *
     * @return bool
     */
    public function hasMultipleEndpoints();
}
