<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;
use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;

/**
 * NativeDocumentMapper maps Solr backend documents per Content translation.
 */
class NativeDocumentMapper implements DocumentMapper
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper
     */
    private $blockFieldMapper;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper
     */
    private $blockTranslationFieldMapper;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper
     */
    private $contentFieldMapper;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper
     */
    private $contentTranslationFieldMapper;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper
     */
    private $locationFieldMapper;

    /**
     * Field registry.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * Location handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * Content type handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Field name generator.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * Creates a new document mapper.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper $blockFieldMapper
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper $blockTranslationFieldMapper
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper $contentFieldMapper
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper $contentTranslationFieldMapper
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper $locationFieldMapper
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(
        ContentFieldMapper $blockFieldMapper,
        ContentTranslationFieldMapper $blockTranslationFieldMapper,
        ContentFieldMapper $contentFieldMapper,
        ContentTranslationFieldMapper $contentTranslationFieldMapper,
        LocationFieldMapper $locationFieldMapper,
        FieldRegistry $fieldRegistry,
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        FieldNameGenerator $fieldNameGenerator
    ) {
        $this->blockFieldMapper = $blockFieldMapper;
        $this->blockTranslationFieldMapper = $blockTranslationFieldMapper;
        $this->contentFieldMapper = $contentFieldMapper;
        $this->contentTranslationFieldMapper = $contentTranslationFieldMapper;
        $this->locationFieldMapper = $locationFieldMapper;
        $this->fieldRegistry = $fieldRegistry;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    public function mapContentBlock(Content $content)
    {
        $locations = $this->locationHandler->loadLocationsByContent($content->versionInfo->contentInfo->id);
        $mainLocation = null;

        $contentType = $this->contentTypeHandler->load($content->versionInfo->contentInfo->contentTypeId);

        $blockFields = $this->getBlockFields($content);
        $contentFields = $this->getContentFields($content);
        $fieldSets = $this->mapContentFields($content, $contentType);
        $documents = array();

        $locationFieldsMap = [];
        foreach ($locations as $location) {
            $locationFieldsMap[$location->id] = $this->getLocationFields($location);
        }

        foreach ($fieldSets as $languageCode => $translationFields) {
            $blockTranslationFields = $this->getBlockTranslationFields(
                $content,
                $languageCode
            );

            $translationLocationDocuments = array();
            foreach ($locations as $location) {
                $translationLocationDocuments[] = new Document(
                    array(
                        'id' => $this->generateLocationDocumentId($location->id, $languageCode),
                        'fields' => array_merge(
                            $blockFields,
                            $locationFieldsMap[$location->id],
                            $blockTranslationFields
                        ),
                    )
                );
            }

            $isMainTranslation = ($content->versionInfo->contentInfo->mainLanguageCode === $languageCode);
            $alwaysAvailable = ($isMainTranslation && $content->versionInfo->contentInfo->alwaysAvailable);
            $contentTranslationFields = $this->getContentTranslationFields(
                $content,
                $languageCode
            );

            $documents[] = new Document(
                array(
                    'id' => $this->generateContentDocumentId(
                        $content->versionInfo->contentInfo->id,
                        $languageCode
                    ),
                    'languageCode' => $languageCode,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isMainTranslation' => $isMainTranslation,
                    'fields' => array_merge(
                        $translationFields['fulltext'],
                        $blockFields,
                        $contentFields,
                        $blockTranslationFields,
                        $contentTranslationFields
                    ),
                    'documents' => $translationLocationDocuments,
                )
            );
        }

        return $documents;
    }

    /**
     * Generates the Solr backend document ID for Content object.
     *
     * Format of id is "content<content-id>lang[<language>]".
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Content's documents (there will be one document per translation).
     * The above is useful when targeting all Content's documents, without
     * the knowledge of it's translations, and thanks to "lang" string it will not
     * risk matching other documents (as was the case in EZP-26484).
     *
     * @param int|string $contentId
     * @param null|string $languageCode
     *
     * @return string
     */
    public function generateContentDocumentId($contentId, $languageCode = null)
    {
        return strtolower("content{$contentId}lang{$languageCode}");
    }

    /**
     * Generates the Solr backend document ID for Location object.
     *
     * Format of id is "content<content-id>lang[<language>]".
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Location's documents (there will be one document per translation).
     * The above is useful when targeting all Location's documents, without
     * the knowledge of it's translations, and thanks to "lang" string it will not
     * risk matching other documents (as was the case in EZP-26484).
     *
     * @param int|string $locationId
     * @param null|string $languageCode
     *
     * @return string
     */
    public function generateLocationDocumentId($locationId, $languageCode = null)
    {
        return strtolower("location{$locationId}lang{$languageCode}");
    }

    /**
     * Maps given Content fields to a map Document fields.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     *
     * @return \eZ\Publish\SPI\Search\Field[][][]
     */
    protected function mapContentFields(Content $content, ContentType $contentType)
    {
        $fieldSets = array();

        foreach ($content->fields as $field) {
            if (!isset($fieldSets[$field->languageCode])) {
                $fieldSets[$field->languageCode] = array(
                    'fulltext' => array(),
                );
            }

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->id !== $field->fieldDefinitionId) {
                    continue;
                }

                $fieldType = $this->fieldRegistry->getType($field->type);
                $indexFields = $fieldType->getIndexData($field, $fieldDefinition);

                foreach ($indexFields as $indexField) {
                    if ($indexField->value === null) {
                        continue;
                    }

                    $documentField = new Field(
                        $name = $this->fieldNameGenerator->getName(
                            $indexField->name,
                            $fieldDefinition->identifier,
                            $contentType->identifier
                        ),
                        $indexField->value,
                        $indexField->type
                    );

                    if ($documentField->type instanceof FieldType\FullTextField) {
                        $fieldSets[$field->languageCode]['fulltext'][] = $documentField;
                    }
                }
            }
        }

        return $fieldSets;
    }

    /**
     * Returns an array of fields for the given $content, to be added to the
     * corresponding block documents.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    private function getBlockFields(Content $content)
    {
        $fields = [];

        if ($this->blockFieldMapper->accept($content)) {
            $fields = $this->blockFieldMapper->mapFields($content);
        }

        return $fields;
    }

    /**
     * Returns an array of fields for the given $content and $languageCode, to be added to the
     * corresponding block documents.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    private function getBlockTranslationFields(Content $content, $languageCode)
    {
        $fields = [];

        if ($this->blockTranslationFieldMapper->accept($content, $languageCode)) {
            $fields = $this->blockTranslationFieldMapper->mapFields($content, $languageCode);
        }

        return $fields;
    }

    /**
     * Returns an array of fields for the given $content, to be added to the corresponding
     * Content document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    private function getContentFields(Content $content)
    {
        $fields = [];

        if ($this->contentFieldMapper->accept($content)) {
            $fields = $this->contentFieldMapper->mapFields($content);
        }

        return $fields;
    }

    /**
     * Returns an array of fields for the given $content and $languageCode, to be added to the
     * corresponding Content document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    private function getContentTranslationFields(Content $content, $languageCode)
    {
        $fields = [];

        if ($this->contentTranslationFieldMapper->accept($content, $languageCode)) {
            $fields = $this->contentTranslationFieldMapper->mapFields($content, $languageCode);
        }

        return $fields;
    }

    /**
     * Returns an array of fields for the given $location, to be added to the corresponding
     * Location document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    private function getLocationFields(Location $location)
    {
        $fields = [];

        if ($this->locationFieldMapper->accept($location)) {
            $fields = $this->locationFieldMapper->mapFields($location);
        }

        return $fields;
    }
}
