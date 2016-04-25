<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\LocationTranslation;

use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\LocationTranslation;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Aggregate implementation of Location translation document field mapper.
 */
class Aggregate extends LocationTranslation
{
    /**
     * An array of aggregated field mappers, sorted by priority.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\LocationTranslation[]
     */
    protected $mappers = [];

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\LocationTranslation[] $mappers
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
     * @param \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\LocationTranslation $mapper
     */
    public function addMapper(LocationTranslation $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Location $content, $languageCode)
    {
        return true;
    }

    public function mapFields(Location $location, $languageCode)
    {
        $fields = [[]];

        foreach ($this->mappers as $mapper) {
            if ($mapper->accept($location, $languageCode)) {
                $fields[] = $mapper->mapFields($location, $languageCode);
            }
        }

        return array_merge(...$fields);
    }
}
