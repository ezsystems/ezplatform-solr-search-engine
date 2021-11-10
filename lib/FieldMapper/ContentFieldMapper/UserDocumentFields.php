<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

final class UserDocumentFields extends ContentFieldMapper
{
    /** @internal */
    public const HASHING_ALGORITHM = 'sha256';

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
                hash(self::HASHING_ALGORITHM, $userField->value->externalData['login']),
                new FieldType\StringField()
            );
        }

        if (isset($userField->value->externalData['email'])) {
            $fields[] = new Field(
                'user_email',
                hash(self::HASHING_ALGORITHM, $userField->value->externalData['email']),
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
