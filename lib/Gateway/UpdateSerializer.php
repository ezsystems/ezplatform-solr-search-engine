<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway;

use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Search\Document;
use XMLWriter;

/**
 * Update serializer converts an array of document objects to the XML string that
 * can be posted to Solr backend for indexing.
 */
class UpdateSerializer
{
    /**
     * @var \eZ\Publish\Core\Search\Common\FieldValueMapper
     */
    protected $fieldValueMapper;

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $nameGenerator;

    /**
     * @param \eZ\Publish\Core\Search\Common\FieldValueMapper $fieldValueMapper
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $nameGenerator
     */
    public function __construct(
        FieldValueMapper $fieldValueMapper,
        FieldNameGenerator $nameGenerator
    ) {
        $this->fieldValueMapper = $fieldValueMapper;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * Create update XML for the given array of $documents.
     *
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     *
     * @return string
     */
    public function serialize(array $documents)
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

    private function writeDocument(XMLWriter $xmlWriter, Document $document)
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

    private function writeField(XMLWriter $xmlWriter, Field $field)
    {
        $values = (array)$this->fieldValueMapper->map($field);
        $name = $this->nameGenerator->getTypedName($field->name, $field->type);

        foreach ($values as $value) {
            $xmlWriter->startElement('field');
            $xmlWriter->writeAttribute('name', $name);
            $xmlWriter->writeAttribute('boost', $field->type->boost);
            $xmlWriter->text($value);
            $xmlWriter->endElement();
        }
    }

    /**
     * Returns a 'dummy' document.
     *
     * This is intended to be indexed as nested document of Content, in order to enforce
     * document block when Content does not have other nested documents (Locations).
     * Not intended to be matched or returned as a search result.
     *
     * For more info see:
     * @link http://grokbase.com/t/lucene/solr-user/14chqr73nv/converting-to-parent-child-block-indexing
     * @link https://issues.apache.org/jira/browse/SOLR-5211
     *
     * @param string $id
     * @return \eZ\Publish\SPI\Search\Document
     */
    private function getNestedDummyDocument($id)
    {
        return new Document(
            [
                'id' => $id . '_nested_dummy',
                'fields' => [
                    new Field(
                        'document_type',
                        'nested_dummy',
                        new FieldType\IdentifierField()
                    ),
                ],
            ]
        );
    }
}
