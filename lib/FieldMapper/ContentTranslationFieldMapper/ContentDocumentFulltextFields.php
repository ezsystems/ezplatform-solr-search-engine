<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps Content fulltext fields to Content document.
 */
class ContentDocumentFulltextFields extends BlockDocumentsContentFields
{
    /**
     * {@inheritdoc}
     */
    protected function getSearchFields(
        Content\Type $contentType,
        Content\Field $field,
        FieldDefinition $fieldDefinition
    ) {
        if (!$fieldDefinition->isSearchable) {
            return [];
        }

        $searchFields = [];
        $fieldType = $this->fieldRegistry->getType($field->type);
        $fullTextData = $fieldType->getFullTextData($field, $fieldDefinition);

        foreach ($fullTextData as $fullTextValue) {
            if (empty($fullTextValue)) {
                continue;
            }

            $searchFields[] = new Field(
                $name = $this->fieldNameGenerator->getName(
                    'fulltext',
                    $fieldDefinition->identifier,
                    $contentType->identifier
                ),
                $fullTextValue,
                new FieldType\FullTextField()
            );
        }

        return $searchFields;
    }
}
