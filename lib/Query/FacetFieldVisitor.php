<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Visits solr results into correct facet and facet builder combination.
 *
 * Use:
 * 1. Directly iterate (or indirectly via aggregate) and call getVistor to get right visitor for a field.
 * 2. Match fac to the field with canMap.
 * 3. Map.
 *
 * NOTE: Will be deprecated in 2.0 and methods will be moved into FacetBuilderVisitor.
 */
interface FacetFieldVisitor
{
    /**
     * Check if visitor is applicable to current facet result, if not return null.
     *
     * @param string $field
     *
     * @return FacetFieldVisitor|null
     */
    public function getFieldVisitor($field);

    /**
     * Check if visitor is applicable to current facet result and facet builder combination.
     *
     * On result, typically call canMap() first and then iterate to figure out right facet builder that needs to be injected into map().
     *
     * @param string $field
     * @param FacetBuilder $facetBuilder
     *
     * @return bool
     */
    public function canMapField($field, FacetBuilder $facetBuilder);

    /**
     * Map Solr facet result back to facet objects.
     *
     * @param string $field
     * @param array $data
     * @param FacetBuilder $facetBuilder
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder);
}
