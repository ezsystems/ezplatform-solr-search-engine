<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;

/**
 * Base class for Location translation document field mappers.
 *
 * Location translation document field mapper maps Location of the Content in a specific
 * translation to the search fields for Location document.
 */
abstract class LocationTranslation extends FieldMapper
{
    /**
     * Indicates if the mapper accepts given $location and $languageCode for mapping.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     * @param string $languageCode
     *
     * @return bool
     */
    abstract public function accept(SPILocation $location, $languageCode);

    /**
     * Maps given $location for $languageCode to an array of search fields.
     *
     * Language code refers to the Content of the given Location.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    abstract public function mapFields(SPILocation $location, $languageCode);
}
