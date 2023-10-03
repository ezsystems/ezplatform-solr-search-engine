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

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;

/**
 * Visits the Section facet builder.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in eZ Platform 4.0.0.
 */
class Section extends FacetBuilderVisitor implements FacetFieldVisitor
{
    /**
     * {@inheritdoc}.
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        return new Facet\SectionFacet(
            [
                'name' => $facetBuilder->name,
                'entries' => $this->mapData($data),
            ]
        );
    }

    /**
     * {@inheritdoc}.
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FacetBuilder\SectionFacetBuilder;
    }

    /**
     * {@inheritdoc}.
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        return [
            'facet.field' => "{!ex=dt key={$fieldId}}content_section_id_id",
            'f.content_section_id_id.facet.limit' => $facetBuilder->limit,
            'f.content_section_id_id.facet.mincount' => $facetBuilder->minCount,
        ];
    }
}
