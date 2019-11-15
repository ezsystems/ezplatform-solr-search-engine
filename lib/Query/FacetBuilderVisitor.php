<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Visits the facet builder tree into a Solr query.
 */
abstract class FacetBuilderVisitor
{
    /**
     * Check if visitor is applicable to current facet result.
     *
     * @deprecated Not needed anymore if visit() correctly used $id param to identify facetBuilder.
     *
     * @param string $field
     *
     * @return bool
     */
    public function canMap($field)
    {
        throw new \LogicException('Deprecated in favour of FacetFieldVisitor, not in use if FacetFieldVisitor is implemented');
    }

    /**
     * Map Solr facet result back to facet objects.
     *
     * @deprecated Will be removed in 2.0, replaced by {@link FacetFieldVisitor::mapField()}.
     *
     * @param string $field
     * @param array $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet
     */
    public function map($field, array $data)
    {
        throw new \LogicException('Deprecated in favour of FacetFieldVisitor, not in use if FacetFieldVisitor is implemented');
    }

    /**
     * Check if visitor is applicable to current facet builder.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return bool
     */
    abstract public function canVisit(FacetBuilder $facetBuilder);

    /**
     * Map field value to a proper Solr representation.
     *
     * @deprecated Will be removed in 2.0, replaced by {@link FacetFieldVisitor::visitBuilder()}.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return string[]
     */
    public function visit(FacetBuilder $facetBuilder)
    {
        throw new \LogicException('Deprecated in favour of FacetFieldVisitor, not in use if FacetFieldVisitor is implemented');
    }

    /**
     * Map Solr return array into a sane hash map.
     *
     * @param array $data
     *
     * @return array
     */
    protected function mapData(array $data)
    {
        $values = array();
        reset($data);
        while ($key = current($data)) {
            $values[$key] = next($data);
            next($data);
        }

        return $values;
    }
}
