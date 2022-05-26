<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Gateway\UpdateSerializer;

use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use Ibexa\Solr\Gateway\UpdateSerializerInterface;
use XMLWriter;

/**
 * @internal
 *
 * @final
 */
class XmlUpdateSerializer extends UpdateSerializer implements UpdateSerializerInterface
{
    public function serialize(array $documents): string
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startElement('add');

        foreach ($documents as $document) {
            if (empty($document->documents)) {
                $document->documents[] = $this->getNestedDummyDocument($document->id);
            }

            $this->writeDocument($xmlWriter, $document);
        }

        $xmlWriter->endElement();

        return $xmlWriter->outputMemory(true);
    }

    private function writeDocument(XMLWriter $xmlWriter, Document $document): void
    {
        $xmlWriter->startElement('doc');

        $this->writeField(
            $xmlWriter,
            new Field(
                'id',
                $document->id,
                new FieldType\IdentifierField()
            )
        );

        foreach ($document->fields as $field) {
            $this->writeField($xmlWriter, $field);
        }

        foreach ($document->documents as $subDocument) {
            $this->writeDocument($xmlWriter, $subDocument);
        }

        $xmlWriter->endElement();
    }

    private function writeField(XMLWriter $xmlWriter, Field $field): void
    {
        $values = (array)$this->fieldValueMapper->map($field);
        $name = $this->nameGenerator->getTypedName($field->getName(), $field->getType());

        $isBoosted = $field->getType()->boost;
        foreach ($values as $value) {
            $xmlWriter->startElement('field');
            $xmlWriter->writeAttribute('name', $name);
            $xmlWriter->writeAttribute('boost', $isBoosted);
            $xmlWriter->text($value);
            $xmlWriter->endElement();
        }
    }

    public function getSupportedFormat(): string
    {
        return 'xml';
    }
}
