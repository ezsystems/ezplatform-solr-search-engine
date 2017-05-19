<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Maps Content fulltext fields to Content document.
 */
class ContentDocumentTranslatedContentNameField extends ContentTranslationFieldMapper
{
    /**
     * Field name, untyped.
     *
     * @var string
     */
    private static $fieldName = 'meta_content__name';

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider
     */
    protected $boostFactorProvider;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider $boostFactorProvider
     */
    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        BoostFactorProvider $boostFactorProvider
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->boostFactorProvider = $boostFactorProvider;
    }

    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    public function mapFields(Content $content, $languageCode)
    {
        if (!isset($content->versionInfo->names[$languageCode])) {
            return [];
        }

        $contentName = $content->versionInfo->names[$languageCode];
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        return [
            new Field(
                self::$fieldName,
                $contentName,
                new FieldType\StringField()
            ),
            new Field(
                self::$fieldName,
                $contentName,
                new FieldType\TextField(
                    [
                        'boost' => $this->boostFactorProvider->getContentMetaFieldBoostFactor(
                            $contentType,
                            'name'
                        ),
                    ]
                )
            ),
        ];
    }
}
