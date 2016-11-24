<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper as BaseContentFieldMapper;
use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps base Content related fields to a Content document.
 */
class ContentDocumentBaseFields extends BaseContentFieldMapper
{
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
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     */
    public function __construct(
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler
    ) {
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler = $sectionHandler;
    }

    public function accept(Content $content)
    {
        return true;
    }

    public function mapFields(Content $content)
    {
        $versionInfo = $content->versionInfo;
        $contentInfo = $content->versionInfo->contentInfo;

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
                'content',
                $contentInfo->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'document_type',
                DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_CONTENT,
                new FieldType\IdentifierField()
            ),
            new Field(
                'type',
                $contentInfo->contentTypeId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'version_no',
                $versionInfo->versionNo,
                new FieldType\IntegerField()
            ),
            new Field(
                'status',
                $versionInfo->status,
                new FieldType\IdentifierField()
            ),
            new Field(
                'name',
                $contentInfo->name,
                new FieldType\StringField()
            ),
            new Field(
                'creator',
                $versionInfo->creatorId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'owner',
                $contentInfo->ownerId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'section',
                $contentInfo->sectionId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'remote_id',
                $contentInfo->remoteId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'modified',
                $contentInfo->modificationDate,
                new FieldType\DateField()
            ),
            new Field(
                'published',
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
                'always_available',
                $contentInfo->alwaysAvailable,
                new FieldType\BooleanField()
            ),
            new Field(
                'owner_user_group',
                $ancestorLocationsContentIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'section_identifier',
                $section->identifier,
                new FieldType\IdentifierField()
            ),
            new Field(
                'section_name',
                $section->name,
                new FieldType\StringField()
            ),
            new Field(
                'group',
                $this->contentTypeHandler->load($contentInfo->contentTypeId)->groupIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'object_state',
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
