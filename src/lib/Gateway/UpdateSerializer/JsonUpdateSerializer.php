<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway\UpdateSerializer;

use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\IdentifierField;
use Ibexa\Solr\Gateway\UpdateSerializerInterface;

/**
 * @internal
 */
final class JsonUpdateSerializer extends UpdateSerializer implements UpdateSerializerInterface
{
    /**
     * @throws \JsonException
     */
    public function serialize(array $documents): string
    {
        $data = [];
        foreach ($documents as $document) {
            if (empty($document->documents)) {
                $document->documents[] = $this->getNestedDummyDocument($document->id);
            }

            $data[] = $this->mapDocumentToData($document);
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    private function mapDocumentToData(Document $document): array
    {
        $data['id'] = $this->fieldValueMapper->map(
            new Field('id', $document->id, new IdentifierField())
        );
        foreach ($document->fields as $field) {
            $fieldName = $this->nameGenerator->getTypedName($field->getName(), $field->getType());
            $value = $this->fieldValueMapper->map($field);
            $data[$fieldName] = $this->buildValue($value, $fieldName, $data);
        }

        foreach ($document->documents as $subDocument) {
            $data['_childDocuments_'][] = $this->mapDocumentToData($subDocument);
        }

        return $data;
    }

    public function getSupportedFormat(): string
    {
        return 'json';
    }

    /**
     * @param mixed|null $value
     *
     * @return mixed
     */
    private function buildValue($value, string $fieldName, array $data)
    {
        return !array_key_exists($fieldName, $data) || !is_array($data[$fieldName])
            ? $value
            // append value(s) to a multivalued type
            : array_merge($data[$fieldName], $value);
    }
}
