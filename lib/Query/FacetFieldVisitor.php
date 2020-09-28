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
 * NOTE: Will be deprecated in 2.0 and methods will be moved into FacetBuilderVisitor.
 */
interface FacetFieldVisitor
{
    /**
     * Map Solr facet result back to facet objects.
     *
     * @param string $field
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
     * @param string $fieldId Id to identify the field in Solr facet.
     *
     * @return string[]
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId);
}
