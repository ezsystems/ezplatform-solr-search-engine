<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Search\Document;
use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;
use Ibexa\Solr\Index\Document\PartialDocument;

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
     * Location handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * Creates a new document mapper.
     */
    public function __construct(
        ContentFieldMapper $blockFieldMapper,
        ContentTranslationFieldMapper $blockTranslationFieldMapper,
        ContentFieldMapper $contentFieldMapper,
        ContentTranslationFieldMapper $contentTranslationFieldMapper,
        LocationFieldMapper $locationFieldMapper,
        LocationHandler $locationHandler
    ) {
        $this->blockFieldMapper = $blockFieldMapper;
        $this->blockTranslationFieldMapper = $blockTranslationFieldMapper;
        $this->contentFieldMapper = $contentFieldMapper;
        $this->contentTranslationFieldMapper = $contentTranslationFieldMapper;
        $this->locationFieldMapper = $locationFieldMapper;
        $this->locationHandler = $locationHandler;
    }

    /**
     * Maps given Content to a Document.
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    public function mapContentBlock(Content $content): array
    {
        $contentInfo = $content->versionInfo->contentInfo;
        $locations = $this->locationHandler->loadLocationsByContent($contentInfo->id);
        $blockFields = $this->getBlockFields($content);
        $contentFields = $this->getContentFields($content);
        $locationFieldsMap = [];

        foreach ($locations as $location) {
            $locationFieldsMap[$location->id] = $this->getLocationFields($location);
        }

        $translationsToUpdate = array_keys($content->versionInfo->names);
        $commonFieldsToUpdateLanguageCodes = array_diff(
            $content->versionInfo->languageCodes,
            $translationsToUpdate
        );

        return array_merge(
            $this->generateDocumentsForTranslationsToUpdate(
                $translationsToUpdate,
                $content,
                $locations,
                $blockFields,
                $locationFieldsMap,
                $contentInfo,
                $contentFields
            ),
            $this->generateDocumentsForCommonFields(
                $commonFieldsToUpdateLanguageCodes,
                $blockFields,
                $contentInfo,
                $contentFields
            )
        );
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
     * @param string|null $languageCode
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
     * @param string|null $languageCode
     *
     * @return string
     */
    public function generateLocationDocumentId($locationId, $languageCode = null)
    {
        return strtolower("location{$locationId}lang{$languageCode}");
    }

    /**
     * Returns an array of fields for the given $content, to be added to the
     * corresponding block documents.
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

    /**
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    private function generateDocumentsForTranslationsToUpdate(
        array $translationsToUpdate,
        Content $content,
        array $locations,
        array $blockFields,
        array $locationFieldsMap,
        Content\ContentInfo $contentInfo,
        array $contentFields
    ): array {
        $documents = [];
        foreach ($translationsToUpdate as $languageCode) {
            $blockTranslationFields = $this->getBlockTranslationFields(
                $content,
                $languageCode
            );

            $translationLocationDocuments = [];
            foreach ($locations as $location) {
                $translationLocationDocuments[] = new Document(
                    [
                        'id' => $this->generateLocationDocumentId($location->id, $languageCode),
                        'fields' => array_merge(
                            $blockFields,
                            $locationFieldsMap[$location->id],
                            $blockTranslationFields
                        ),
                    ]
                );
            }

            $isMainTranslation = ($contentInfo->mainLanguageCode === $languageCode);
            $alwaysAvailable = ($isMainTranslation && $contentInfo->alwaysAvailable);
            $contentTranslationFields = $this->getContentTranslationFields(
                $content,
                $languageCode
            );

            $documents[] = new Document(
                [
                    'id' => $this->generateContentDocumentId(
                        $contentInfo->id,
                        $languageCode
                    ),
                    'languageCode' => $languageCode,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isMainTranslation' => $isMainTranslation,
                    'fields' => array_merge(
                        $blockFields,
                        $contentFields,
                        $blockTranslationFields,
                        $contentTranslationFields
                    ),
                    'documents' => $translationLocationDocuments,
                ]
            );
        }

        return $documents;
    }

    /**
     * @param string[] $translations
     *
     * @return \Ibexa\Solr\Index\Document\PartialDocument[]
     */
    private function generateDocumentsForCommonFields(
        array $translations,
        array $blockFields,
        Content\ContentInfo $contentInfo,
        array $contentFields
    ): array {
        $documents = [];
        foreach ($translations as $languageCode) {
            $documents[] = new PartialDocument(
                $this->generateContentDocumentId(
                    $contentInfo->id,
                    $languageCode
                ),
                array_merge(
                    $blockFields,
                    $contentFields,
                )
            );
        }

        return $documents;
    }
}
