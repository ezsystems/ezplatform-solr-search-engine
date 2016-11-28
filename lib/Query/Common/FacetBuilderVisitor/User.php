<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\FacetBuilderVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Visits the User facet builder.
 */
class User extends FacetBuilderVisitor
{
    /**
     * Check if visitor is applicable to current facet result.
     *
     * @param string $field
     *
     * @return bool
     */
    public function canMap($field)
    {
        return $field === 'content_version_creator_user_id_id';
    }

    /**
     * Map Solr facet result back to facet objects.
     *
     * @param string $field
     * @param array $data
     *
     * @return Facet
     */
    public function map($field, array $data)
    {
        return new Facet\UserFacet(
            array(
                'name' => 'creator',
                'entries' => $this->mapData($data),
            )
        );
    }

    /**
     * Check if visitor is applicable to current facet builder.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return bool
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FacetBuilder\UserFacetBuilder;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param FacetBuilder $facetBuilder;
     *
     * @return string
     */
    public function visit(FacetBuilder $facetBuilder)
    {
        return array(
            'facet.field' => 'content_version_creator_user_id_id',
            'f.content_version_creator_user_id_id.facet.limit' => $facetBuilder->limit,
            'f.content_version_creator_user_id_id.facet.mincount' => $facetBuilder->minCount,
        );
    }
}
