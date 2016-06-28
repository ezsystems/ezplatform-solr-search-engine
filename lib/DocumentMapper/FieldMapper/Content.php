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
use eZ\Publish\SPI\Persistence\Content as SPIContent;

/**
 * Base class for Content document field mapper.
 *
 * Content document field mapper maps Content to the search fields for Content document.
 */
abstract class Content extends FieldMapper
{
    /**
     * Indicates if the mapper accepts the given $content for mapping.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return bool
     */
    abstract public function accept(SPIContent $content);

    /**
     * Maps given $content to an array of search fields.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    abstract public function mapFields(SPIContent $content);
}
