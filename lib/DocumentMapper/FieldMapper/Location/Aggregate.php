<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\Location;

use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\Location as LocationMapper;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Aggregate implementation of Location document field mapper.
 */
class Aggregate extends LocationMapper
{
    /**
     * An array of aggregated field mappers, sorted by priority.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\Location[]
     */
    protected $mappers = [];

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\Location[] $mappers
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
     * @param \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper\FieldMapper\Location $mapper
     */
    public function addMapper(LocationMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Location $location)
    {
        return true;
    }

    public function mapFields(Location $location)
    {
        $fields = [];

        foreach ($this->mappers as $mapper) {
            if ($mapper->accept($location)) {
                $fields = array_merge($fields, $mapper->mapFields($location));
            }
        }

        return $fields;
    }
}
