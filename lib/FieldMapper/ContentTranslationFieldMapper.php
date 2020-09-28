<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper;

use eZ\Publish\SPI\Persistence\Content as SPIContent;

/**
 * Base class for Content translation document field mapper.
 *
 * Content translation document field mapper maps Content in a specific translation to the
 * search fields for Content document.
 */
abstract class ContentTranslationFieldMapper
{
    /**
     * Indicates if the mapper accepts given $content and $languageCode for mapping.
     *
     * @param string $languageCode
     *
     * @return bool
     */
    abstract public function accept(SPIContent $content, $languageCode);

    /**
     * Maps given $content for $languageCode to an array of search fields.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    abstract public function mapFields(SPIContent $content, $languageCode);
}
