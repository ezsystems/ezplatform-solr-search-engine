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

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Mapper maps Content and Location objects to a Document objects, representing a
 * document in Solr index storage.
 *
 * Note that custom implementations might need to be accompanied by custom schema.
 */
interface DocumentMapper
{
    /**
     * Identifier of Content documents.
     *
     * @var string
     */
    const DOCUMENT_TYPE_IDENTIFIER_CONTENT = 'content';

    /**
     * Identifier of Location documents.
     *
     * @var string
     */
    const DOCUMENT_TYPE_IDENTIFIER_LOCATION = 'location';

    /**
     * Maps given Content and it's Locations to a collection of nested Documents,
     * one per translation.
     *
     * Each Content Document contains nested Documents representing it's Locations.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    public function mapContentBlock(Content $content);

    /**
     * Generates the Solr backend document ID for Content object.
     *
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Content's documents (there will be one document per translation).
     * The above is useful when targeting all Content's documents, without
     * the knowledge of it's translations.
     *
     * @param int|string $contentId
     * @param string $languageCode
     *
     * @return string
     */
    public function generateContentDocumentId($contentId, $languageCode = null);

    /**
     * Generates the Solr backend document ID for Location object.
     *
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Location's documents (there will be one document per translation).
     * The above is useful when targeting all Location's documents, without
     * the knowledge of it's Content's translations.
     *
     * @param int|string $locationId
     * @param string $languageCode
     *
     * @return string
     */
    public function generateLocationDocumentId($locationId, $languageCode = null);
}
