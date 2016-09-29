<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;
use eZ\Publish\SPI\Persistence\Content;

/**
 * Aggregate implementation of Content translation document field mapper.
 */
class Aggregate extends ContentTranslationFieldMapper
{
    /**
     * An array of aggregated field mappers, sorted by priority.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper[]
     */
    protected $mappers = [];

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper[] $mappers
     *        An array of mappers, sorted by priority.
     */
    public function __construct(array $mappers = [])
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    /**
     * Adds given $mapper to the internal array as the next one in priority.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper $mapper
     */
    public function addMapper(ContentTranslationFieldMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    public function mapFields(Content $content, $languageCode)
    {
        $fields = [];

        foreach ($this->mappers as $mapper) {
            if ($mapper->accept($content, $languageCode)) {
                $fields = array_merge($fields, $mapper->mapFields($content, $languageCode));
            }
        }

        return $fields;
    }
}
