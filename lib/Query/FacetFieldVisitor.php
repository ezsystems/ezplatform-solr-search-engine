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
 * Visits Solr results into correct facet and facet builder combination.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in eZ Platform 4.0.0.
 */
interface FacetFieldVisitor
{
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

    /**
     * Map field value to a proper Solr representation.
     *
     * Example:
     *        return array(
     *            'facet.field' => "{!ex=dt key=${fieldId}}content_type_id_id",
     *            'f.content_type_id_id.facet.limit' => $facetBuilder->limit,
     *            'f.content_type_id_id.facet.mincount' => $facetBuilder->minCount,
     *        );
     *
     * @param FacetBuilder $facetBuilder
     * @param string $fieldId Id to identify the field in Solr facet.
     *
     * @return string[]
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId);
}
