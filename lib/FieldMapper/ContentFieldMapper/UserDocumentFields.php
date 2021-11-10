<?php
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

final class UserDocumentFields extends ContentFieldMapper
{
    public function accept(SPIContent $content): bool
    {
        return $this->getUserField($content) !== null;
    }

    public function mapFields(SPIContent $content): array
    {
        $userField = $this->getUserField($content);
        if ($userField === null) {
            return [];
        }

        $fields = [];

        if (isset($userField->value->externalData['login'])) {
            $fields[] = new Field(
                'user_login',
                hash('sha256', $userField->value->externalData['login']),
                new FieldType\StringField()
            );
        }

        if (isset($userField->value->externalData['email'])) {
            $fields[] = new Field(
                'user_email',
                hash('sha256', $userField->value->externalData['email']),
                new FieldType\StringField()
            );
        }

        return $fields;
    }

    private function getUserField(SPIContent $content): ?SPIContent\Field
    {
        foreach ($content->fields as $field) {
            if ($field->type === 'ezuser') {
                return $field;
            }
        }

        return null;
    }
}
