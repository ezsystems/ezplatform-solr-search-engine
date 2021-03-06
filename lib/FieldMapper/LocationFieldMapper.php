<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper;

use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;

/**
 * Base class for Location document field mappers.
 *
 * Location document field mapper maps Location to the search fields for Location document.
 */
abstract class LocationFieldMapper
{
    /**
     * Indicates if the mapper accepts given $location for mapping.
     *
     * @return bool
     */
    abstract public function accept(SPILocation $location);

    /**
     * Maps given $location to an array of search fields.
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    abstract public function mapFields(SPILocation $location);
}
