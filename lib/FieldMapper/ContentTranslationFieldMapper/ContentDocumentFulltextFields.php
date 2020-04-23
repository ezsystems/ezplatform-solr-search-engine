<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

use OutOfBoundsException;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\IndexingDepthProvider;

/**
 * Maps Content fulltext fields to Content document.
 */
class ContentDocumentFulltextFields extends ContentTranslationFieldMapper
{
    /**
     * Field name, untyped.
     *
     * @var string
     */
    private static $fieldName = 'meta_content__text';

    /**
     * Field of related content name, untyped.
     *
     * @var string
     */
    private static $relatedContentFieldName = 'meta_related_content_%d__text';

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider
     */
    protected $boostFactorProvider;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\IndexingDepthProvider
     */
    protected $indexingDepthProvider;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider $boostFactorProvider
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\IndexingDepthProvider $indexingDepthProvider
     */
    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        ContentHandler $contentHandler,
        FieldRegistry $fieldRegistry,
        FieldNameGenerator $fieldNameGenerator,
        BoostFactorProvider $boostFactorProvider,
        IndexingDepthProvider $indexingDepthProvider
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->contentHandler = $contentHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->boostFactorProvider = $boostFactorProvider;
        $this->indexingDepthProvider = $indexingDepthProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function mapFields(Content $content, $languageCode)
    {
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        $maxDepth = $this->indexingDepthProvider->getMaxDepthForContent(
            $contentType
        );

        return $this->doMapFields($content, $contentType, $languageCode, $maxDepth);
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     * @param string $languageCode
     * @param int $maxDepth
     * @param int $depth
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function doMapFields(Content $content, ContentType $contentType, $languageCode, $maxDepth, $depth = 0)
    {
        $fields = [];

        foreach ($content->fields as $field) {
            if ($field->languageCode !== $languageCode) {
                continue;
            }

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->id !== $field->fieldDefinitionId) {
                    continue;
                }

                try {
                    $fieldType = $this->fieldRegistry->getType($field->type);
                } catch (OutOfBoundsException $e) {
                    continue;
                }

                $indexFields = $fieldType->getIndexData($field, $fieldDefinition);

                foreach ($indexFields as $indexField) {
                    if ($indexField->value === null) {
                        continue;
                    }

                    if (!$indexField->type instanceof FieldType\FullTextField || !$fieldDefinition->isSearchable) {
                        continue;
                    }

                    $fields[] = new Field(
                        $this->getIndexFieldName($depth),
                        $indexField->value,
                        $this->getIndexFieldType($contentType)
                    );
                }
            }
        }

        if ($depth < $maxDepth) {
            $relatedFields = $this->doMapRelatedFields($content, $languageCode, $maxDepth, $depth + 1);
            foreach ($relatedFields as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Maps given $content relations to an array of search fields.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $sourceContent
     * @param string $languageCode
     * @param int $maxDepth
     * @param int $depth
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function doMapRelatedFields(Content $sourceContent, $languageCode, $maxDepth, $depth)
    {
        $relations = $this->contentHandler->loadRelations($sourceContent->versionInfo->contentInfo->id);

        $relatedContents = $this->contentHandler->loadContentList(
            array_map(function (Content\Relation $relation) {
                return $relation->destinationContentId;
            }, $relations)
        );

        $contentTypes = $this->contentTypeHandler->loadContentTypeList(
            array_map(function (Content $content) {
                return $content->versionInfo->contentInfo->contentTypeId;
            }, $relatedContents)
        );

        $fields = [];
        foreach ($relatedContents as $relatedContent) {
            $contentTypeId = $relatedContent->versionInfo->contentInfo->contentTypeId;

            $relatedFields = $this->doMapFields($relatedContent, $contentTypes[$contentTypeId], $languageCode, $maxDepth, $depth);
            foreach ($relatedFields as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns field name base on given depth.
     *
     * @param int $depth
     *
     * @return string
     */
    private function getIndexFieldName(int $depth): string
    {
        if ($depth === 0) {
            return self::$fieldName;
        }

        return sprintf(self::$relatedContentFieldName, $depth);
    }

    /**
     * Return index field type for the given $contentType.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     *
     * @return \eZ\Publish\SPI\Search\FieldType
     */
    private function getIndexFieldType(ContentType $contentType)
    {
        $newFieldType = new FieldType\TextField();
        $newFieldType->boost = $this->boostFactorProvider->getContentMetaFieldBoostFactor(
            $contentType,
            'text'
        );

        return $newFieldType;
    }
}
