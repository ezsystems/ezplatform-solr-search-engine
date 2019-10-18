<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

/**
 * Indexes information on whether Content field is empty.
 */
class ContentDocumentEmptyFields extends ContentTranslationFieldMapper
{
    public const IS_EMPTY_NAME = 'is_empty';

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    private $contentTypeHandler;

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    private $fieldNameGenerator;

    /**
     * @var \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    private $fieldTypeRegistry;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     * @param \eZ\Publish\Core\Persistence\FieldTypeRegistry $fieldTypeRegistry
     */
    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        FieldNameGenerator $fieldNameGenerator,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param $languageCode
     *
     * @return bool
     */
    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param string $languageCode
     *
     * @return array|\eZ\Publish\SPI\Search\Field[]
     */
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
                if ($fieldDefinition->isRequired) {
                    continue;
                }

                /** @var \eZ\Publish\Core\Persistence\FieldType $fieldType */
                $fieldType = $this->fieldTypeRegistry->getFieldType($fieldDefinition->fieldType);
                $fields[] = new Field(
                    $name = $this->fieldNameGenerator->getName(
                        self::IS_EMPTY_NAME,
                        $fieldDefinition->identifier
                    ),
                    ($fieldType->getEmptyValue() == $field->value),
                    new FieldType\BooleanField()
                );
            }
        }

        return $fields;
    }
}
