<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor;

use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor;
use RuntimeException;

/**
 * The Native Result Extractor extracts the value object from the data
 * returned by the Solr backend.
 */
class NativeResultExtractor extends ResultExtractor
{
    /**
     * Extracts value object from $hit returned by Solr backend.
     *
     * @throws \RuntimeException If search $hit could not be handled
     *
     * @param mixed $hit
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function extractHit($hit)
    {
        if ($hit->document_type_id === 'content') {
            return $this->extractContentInfoFromHit($hit);
        }

        if ($hit->document_type_id === 'location') {
            return $this->extractLocationFromHit($hit);
        }

        throw new RuntimeException("Could not extract: document of type '{$hit->document_type_id}' is not handled.");
    }

    /**
     * @param mixed $hit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    protected function extractContentInfoFromHit($hit)
    {
        $contentInfo = new ContentInfo(
            [
                'id' => (int)$hit->content_id_id,
                'name' => $hit->content_name_s,
                'contentTypeId' => (int)$hit->content_type_id_id,
                'sectionId' => (int)$hit->content_section_id_id,
                'currentVersionNo' => $hit->content_version_no_i,
                'isPublished' => true,
                'ownerId' => (int)$hit->content_owner_user_id_id,
                'modificationDate' => strtotime($hit->content_modification_date_dt),
                'publicationDate' => strtotime($hit->content_publication_date_dt),
                'alwaysAvailable' => $hit->content_always_available_b,
                'remoteId' => $hit->content_remote_id_id,
                'mainLanguageCode' => $hit->content_main_language_code_s,
            ]
        );

        if (isset($hit->main_location_id)) {
            $contentInfo->mainLocationId = (int)$hit->main_location_id;
        }

        return $contentInfo;
    }

    /**
     * @param mixed $hit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    protected function extractLocationFromHit($hit)
    {
        return new Location(
            [
                'id' => (int)$hit->location_id,
                'priority' => $hit->priority_i,
                'hidden' => $hit->hidden_b,
                'invisible' => $hit->invisible_b,
                'remoteId' => $hit->remote_id_id,
                'contentId' => (int)$hit->content_id_id,
                'parentId' => (int)$hit->parent_id_id,
                'pathString' => $hit->path_string_id,
                'depth' => $hit->depth_i,
                'sortField' => (int)$hit->sort_field_id,
                'sortOrder' => (int)$hit->sort_order_id,
            ]
        );
    }
}
