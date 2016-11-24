<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps Content fulltext fields to Content document.
 */
class ContentDocumentFulltextFields extends BlockDocumentsContentFields
{
    protected function appendField(array &$fields, Field $documentField)
    {
        if ($documentField->type instanceof FieldType\FullTextField) {
            $fields[] = $documentField;
        }
    }
}
