<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper as BaseLocationFieldMapper;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps base Location related fields to a Location document.
 */
class LocationDocumentBaseFields extends BaseLocationFieldMapper
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     */
    public function __construct(ContentHandler $contentHandler)
    {
        $this->contentHandler = $contentHandler;
    }

    public function accept(Location $location)
    {
        return true;
    }

    public function mapFields(Location $location)
    {
        $contentInfo = $this->contentHandler->loadContentInfo($location->contentId);

        return [
            new Field(
                'location',
                $location->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'document_type',
                DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_LOCATION,
                new FieldType\IdentifierField()
            ),
            new Field(
                'priority',
                $location->priority,
                new FieldType\IntegerField()
            ),
            new Field(
                'hidden',
                $location->hidden,
                new FieldType\BooleanField()
            ),
            new Field(
                'invisible',
                $location->invisible,
                new FieldType\BooleanField()
            ),
            new Field(
                'remote_id',
                $location->remoteId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'parent_id',
                $location->parentId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'path_string',
                $location->pathString,
                new FieldType\IdentifierField()
            ),
            new Field(
                'depth',
                $location->depth,
                new FieldType\IntegerField()
            ),
            new Field(
                'sort_field',
                $location->sortField,
                new FieldType\IdentifierField()
            ),
            new Field(
                'sort_order',
                $location->sortOrder,
                new FieldType\IdentifierField()
            ),
            new Field(
                'is_main_location',
                ($location->id == $contentInfo->mainLocationId),
                new FieldType\BooleanField()
            ),
        ];
    }
}
