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
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;

/**
 * Visits the facet builder tree into a Solr query.
 */
class Aggregate extends FacetBuilderVisitor implements FacetFieldVisitor
{
    /**
     * Array of available visitors.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor[]
     */
    protected $visitors = [];

    /**
     * Construct from optional visitor array.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor[] $visitors
     */
    public function __construct(array $visitors = [])
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds visitor.
     */
    public function addVisitor(FacetBuilderVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * {@inheritdoc}.
     *
     * @deprecated Internal support for nullable $facetBuilder will be removed in 2.0, here now to support facetBuilders
     *             that has not adapted yet.
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder = null)
    {
        foreach ($this->visitors as $visitor) {
            if ($facetBuilder && $visitor instanceof FacetFieldVisitor && $visitor->canVisit($facetBuilder)) {
                return $visitor->mapField($field, $data, $facetBuilder);
            } elseif (!$facetBuilder && $visitor->canMap($field)) {
                return $visitor->map($field, $data);
            }
        }

        throw new \OutOfRangeException('No visitor available for: ' . $field);
    }

    /**
     * {@inheritdoc}.
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return true;
    }

    /**
     * {@inheritdoc}.
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($facetBuilder)) {
                return $visitor instanceof FacetFieldVisitor ?
                    $visitor->visitBuilder($facetBuilder, $fieldId) :
                    $visitor->visit($facetBuilder);
            }
        }

        // Ignore unsupported FacetBuilders, don't block the query for it
        // ref: https://github.com/ezsystems/ezpublish-kernel/commit/435624d6fb8aa03ec219818ff7cb6755944b8d7b
        return [];
    }
}
