<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

/**
 * Maps base Content related fields to block document (Content and Location).
 */
class BlockDocumentsBaseContentFields extends ContentFieldMapper
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
                'content_id',
                $contentInfo->id,
                new FieldType\IdentifierField()
            ),
            // explicit integer representation to allow sorting
            new Field(
                'content_id_normalized',
                $contentInfo->id,
                new FieldType\IntegerField()
            ),
            new Field(
                'content_type_id',
                $contentInfo->contentTypeId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_version_no',
                $versionInfo->versionNo,
                new FieldType\IntegerField()
            ),
            new Field(
                'content_version_status',
                $versionInfo->status,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_name',
                $contentInfo->name,
                new FieldType\StringField()
            ),
            new Field(
                'content_version_creator_user_id',
                $versionInfo->creatorId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_owner_user_id',
                $contentInfo->ownerId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_section_id',
                $contentInfo->sectionId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_remote_id',
                $contentInfo->remoteId,
                new FieldType\RemoteIdentifierField()
            ),
            new Field(
                'content_modification_date',
                $contentInfo->modificationDate,
                new FieldType\DateField()
            ),
            new Field(
                'content_publication_date',
                $contentInfo->publicationDate,
                new FieldType\DateField()
            ),
            new Field(
                'content_language_codes',
                $versionInfo->languageCodes,
                new FieldType\MultipleStringField()
            ),
            new Field(
                'content_language_codes_raw',
                $versionInfo->languageCodes,
                new FieldType\MultipleIdentifierField(['raw' => true])
            ),
            new Field(
                'content_main_language_code',
                $contentInfo->mainLanguageCode,
                new FieldType\StringField()
            ),
            new Field(
                'content_always_available',
                $contentInfo->alwaysAvailable,
                new FieldType\BooleanField()
            ),
            new Field(
                'content_owner_user_group_ids',
                $ancestorLocationsContentIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'content_section_identifier',
                $section->identifier,
                new FieldType\StringField()
            ),
            new Field(
                'content_section_name',
                $section->name,
                new FieldType\StringField()
            ),
            new Field(
                'content_type_group_ids',
                $this->contentTypeHandler->load($contentInfo->contentTypeId)->groupIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'content_object_state_ids',
                $this->getObjectStateIds($contentInfo->id),
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'content_object_state_identifiers',
                $this->getObjectStateIdentifiers($contentInfo->id),
                new FieldType\MultipleStringField()
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
        $objectStateIds = [];

        foreach ($this->objectStateHandler->loadAllGroups() as $objectStateGroup) {
            try {
                $objectStateIds[] = $this->objectStateHandler->getContentState(
                    $contentId,
                    $objectStateGroup->id
                )->id;
            } catch (NotFoundException $e) {
                // // Ignore empty object state groups
            }
        }

        return $objectStateIds;
    }

    /**
     * @return string[]
     */
    protected function getObjectStateIdentifiers(int $contentId): array
    {
        $identifiers = [];

        foreach ($this->objectStateHandler->loadAllGroups() as $objectStateGroup) {
            $identifiers[] = sprintf(
                '%s:%s',
                $objectStateGroup->identifier,
                $this->objectStateHandler->getContentState(
                    $contentId,
                    $objectStateGroup->id
                )->identifier
            );
        }

        return $identifiers;
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
        $ancestorLocationContentIds = [];
        $ancestorLocationIds = [];

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
