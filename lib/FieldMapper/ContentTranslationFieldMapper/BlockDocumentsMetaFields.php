<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper as BaseContentTranslationFieldMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps meta fields to block documents (Content and Location).
 */
class BlockDocumentsMetaFields extends BaseContentTranslationFieldMapper
{
    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    public function mapFields(Content $content, $languageCode)
    {
        return [
            new Field(
                'meta_indexed_language_code',
                $languageCode,
                new FieldType\StringField()
            ),
            new Field(
                'meta_indexed_is_main_translation',
                ($languageCode === $content->versionInfo->contentInfo->mainLanguageCode),
                new FieldType\BooleanField()
            ),
            new Field(
                'meta_indexed_is_main_translation_and_always_available',
                (
                    ($languageCode === $content->versionInfo->contentInfo->mainLanguageCode) &&
                    $content->versionInfo->contentInfo->alwaysAvailable
                ),
                new FieldType\BooleanField()
            ),
        ];
    }
}
