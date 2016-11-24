<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps Content related fields to a Location document.
 */
class LocationDocumentContentFields extends LocationFieldMapper
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     */
    public function __construct(
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler
    ) {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler = $sectionHandler;
    }

    public function accept(Location $location)
    {
        return true;
    }

    public function mapFields(Location $location)
    {
        $contentInfo = $this->contentHandler->loadContentInfo($location->contentId);
        $versionInfo = $this->contentHandler->loadVersionInfo(
            $location->contentId,
            $contentInfo->currentVersionNo
        );

        // UserGroups and Users are Content, but permissions cascade is achieved through
        // Locations hierarchy. We index all ancestor Location Content ids of all
        // Locations of an owner.
        $ancestorLocationsContentIds = $this->getAncestorLocationsContentIds(
            $contentInfo->ownerId
        );
        // Add owner user id as it can also be considered as user group.
        $ancestorLocationsContentIds[] = $contentInfo->ownerId;

        $section = $this->sectionHandler->load($contentInfo->sectionId);

        return [
            new Field(
                'content_id',
                $contentInfo->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_type',
                $contentInfo->contentTypeId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_version_no',
                $versionInfo->versionNo,
                new FieldType\IntegerField()
            ),
            new Field(
                'content_status',
                $versionInfo->status,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_name',
                $contentInfo->name,
                new FieldType\StringField()
            ),
            new Field(
                'content_creator',
                $versionInfo->creatorId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_owner',
                $contentInfo->ownerId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_section',
                $contentInfo->sectionId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_remote_id',
                $contentInfo->remoteId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_modified',
                $contentInfo->modificationDate,
                new FieldType\DateField()
            ),
            new Field(
                'content_published',
                $contentInfo->publicationDate,
                new FieldType\DateField()
            ),
            new Field(
                'language_code',
                array_keys($versionInfo->names),
                new FieldType\MultipleStringField()
            ),
            new Field(
                'main_language_code',
                $contentInfo->mainLanguageCode,
                new FieldType\StringField()
            ),
            new Field(
                'content_always_available',
                $contentInfo->alwaysAvailable,
                new FieldType\BooleanField()
            ),
            new Field(
                'content_owner_user_group',
                $ancestorLocationsContentIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'content_section_identifier',
                $section->identifier,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_section_name',
                $section->name,
                new FieldType\StringField()
            ),
            new Field(
                'content_group',
                $this->contentTypeHandler->load($contentInfo->contentTypeId)->groupIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'content_object_state',
                $this->getObjectStateIds($contentInfo->id),
                new FieldType\MultipleIdentifierField()
            ),
        ];
    }

    /**
     * Returns an array of object state ids of a Content with given $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getObjectStateIds($contentId)
    {
        $objectStateIds = array();

        foreach ($this->objectStateHandler->loadAllGroups() as $objectStateGroup) {
            $objectStateIds[] = $this->objectStateHandler->getContentState(
                $contentId,
                $objectStateGroup->id
            )->id;
        }

        return $objectStateIds;
    }

    /**
     * Returns Content ids of all ancestor Locations of all Locations
     * of a Content with given $contentId.
     *
     * Used to determine user groups of a user with $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getAncestorLocationsContentIds($contentId)
    {
        $locations = $this->locationHandler->loadLocationsByContent($contentId);
        $ancestorLocationContentIds = array();
        $ancestorLocationIds = array();

        foreach ($locations as $location) {
            $locationIds = explode('/', trim($location->pathString, '/'));
            // Remove Location of Content with $contentId
            array_pop($locationIds);
            // Remove Root Location id (id==1 in legacy DB)
            array_shift($locationIds);

            $ancestorLocationIds = array_merge($ancestorLocationIds, $locationIds);
        }

        foreach (array_unique($ancestorLocationIds) as $locationId) {
            $location = $this->locationHandler->load($locationId);

            $ancestorLocationContentIds[$location->contentId] = true;
        }

        return array_keys($ancestorLocationContentIds);
    }
}
