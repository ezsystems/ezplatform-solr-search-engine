<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps Location related fields to a Content document.
 */
class ContentDocumentLocationFields extends ContentFieldMapper
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     */
    public function __construct(LocationHandler $locationHandler)
    {
        $this->locationHandler = $locationHandler;
    }

    public function accept(Content $content)
    {
        return true;
    }

    public function mapFields(Content $content)
    {
        $locations = $this->locationHandler->loadLocationsByContent($content->versionInfo->contentInfo->id);
        $mainLocation = null;
        $isSomeLocationVisible = false;
        $locationData = [];
        $fields = [];

        foreach ($locations as $location) {
            $locationData['ids'][] = $location->id;
            $locationData['parent_ids'][] = $location->parentId;
            $locationData['remote_ids'][] = $location->remoteId;
            $locationData['path_strings'][] = $location->pathString;

            if ($location->id == $content->versionInfo->contentInfo->mainLocationId) {
                $mainLocation = $location;
            }

            if (!$location->hidden && !$location->invisible) {
                $isSomeLocationVisible = true;
            }
        }

        if (!empty($locationData)) {
            $fields[] = new Field(
                'location_id',
                $locationData['ids'],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_parent_id',
                $locationData['parent_ids'],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_remote_id',
                $locationData['remote_ids'],
                new FieldType\MultipleRemoteIdentifierField()
            );
            $fields[] = new Field(
                'location_path_string',
                $locationData['path_strings'],
                new FieldType\MultipleIdentifierField()
            );
        }

        if ($mainLocation !== null) {
            $fields[] = new Field(
                'main_location',
                $mainLocation->id,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_parent',
                $mainLocation->parentId,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_remote_id',
                $mainLocation->remoteId,
                new FieldType\RemoteIdentifierField()
            );
            $fields[] = new Field(
                'main_location_visible',
                !$mainLocation->hidden && !$mainLocation->invisible,
                new FieldType\BooleanField()
            );
            $fields[] = new Field(
                'main_location_path',
                $mainLocation->pathString,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_depth',
                $mainLocation->depth,
                new FieldType\IntegerField()
            );
            $fields[] = new Field(
                'main_location_priority',
                $mainLocation->priority,
                new FieldType\IntegerField()
            );
        }

        $fields[] = new Field(
            'location_visible',
            $isSomeLocationVisible,
            new FieldType\BooleanField()
        );

        return $fields;
    }
}
