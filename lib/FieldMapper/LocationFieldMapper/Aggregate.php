<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;

use eZ\Publish\SPI\Persistence\Content\Location;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;

/**
 * Aggregate implementation of Location document field mapper.
 */
class Aggregate extends LocationFieldMapper
{
    /**
     * An array of aggregated field mappers, sorted by priority.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper[]
     */
    protected $mappers = [];

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper[] $mappers
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
     */
    public function addMapper(LocationFieldMapper $mapper)
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
