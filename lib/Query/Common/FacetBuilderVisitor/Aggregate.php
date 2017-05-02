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
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

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
    protected $visitors = array();

    /**
     * Construct from optional visitor array.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor[] $visitors
     */
    public function __construct(array $visitors = array())
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds visitor.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor $visitor
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

        throw new NotImplementedException(
            'No visitor available for: ' . get_class($facetBuilder)
        );
    }
}
