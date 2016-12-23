<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps Content fields to block documents (Content and Location).
 */
class BlockDocumentsContentFields extends ContentTranslationFieldMapper
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        FieldRegistry $fieldRegistry,
        FieldNameGenerator $fieldNameGenerator
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    public function mapFields(Content $content, $languageCode)
    {
        $fields = [];
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        foreach ($content->fields as $field) {
            if ($field->languageCode !== $languageCode) {
                continue;
            }

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->id !== $field->fieldDefinitionId) {
                    continue;
                }

                $fields = array_merge(
                    $fields,
                    $this->getSearchFields($contentType, $field, $fieldDefinition)
                );
            }
        }

        return $fields;
    }

    /**
     * Get search fields for the given Content Field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    protected function getSearchFields(
        Content\Type $contentType,
        Content\Field $field,
        FieldDefinition $fieldDefinition
    ) {
        $searchFields = [];
        $fieldType = $this->fieldRegistry->getType($field->type);
        $filterData = $fieldType->getFilterData($field, $fieldDefinition);

        foreach ($filterData as $filterField) {
            if ($filterField->value === null) {
                continue;
            }

            $searchFields[] = new Field(
                $name = $this->fieldNameGenerator->getName(
                    $filterField->name,
                    $fieldDefinition->identifier,
                    $contentType->identifier
                ),
                $filterField->value,
                $filterField->type
            );
        }

        return $searchFields;
    }
}
