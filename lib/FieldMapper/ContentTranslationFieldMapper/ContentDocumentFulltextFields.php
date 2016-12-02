<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

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
     *
     * Overridden to append only full text fields, instead of everything but full text fields
     * in the base implementation.
     */
    protected function appendField(array &$fields, FieldDefinition $fieldDefinition, Field $documentField)
    {
        if ($documentField->type instanceof FieldType\FullTextField && $fieldDefinition->isSearchable) {
            $fields[] = $documentField;
        }
    }
}
