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
use eZ\Publish\SPI\Search\FieldType;

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

                    $this->appendField($fields, $fieldDefinition, $documentField);
                }
            }
        }

        return $fields;
    }

    /**
     * Appends given $documentField to $fields collection, depending on a condition.
     *
     * @param array $fields
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\SPI\Search\Field $documentField
     */
    protected function appendField(array &$fields, FieldDefinition $fieldDefinition, Field $documentField)
    {
        if (!$documentField->type instanceof FieldType\FullTextField) {
            $fields[] = $documentField;
        }
    }
}
