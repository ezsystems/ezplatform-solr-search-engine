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
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
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
     */
    public function getFieldVisitor($field)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor instanceof FacetFieldVisitor) {
                if ($visitor = $visitor->getFieldVisitor($field)) {
                    return $visitor;
                }
            } elseif ($visitor->canMap($field)) {
                return $visitor;
            }
        }

        throw new \OutOfRangeException('No visitor available for: ' . $field);
    }

    /**
     * {@inheritdoc}.
     */
    public function canMapField($field, FacetBuilder $facetBuilder)
    {
        // Return false, as caller should call getFieldVisitor() and directly use that visitor.
        return false;
    }

    /**
     * {@inheritdoc}.
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        throw new \LogicException(
            'mapField() should not be called on aggregate, call getFieldVisitor() and directly call mapField() ' .
            'on returned  once you picked the right FacetBuilder using canMapField().'
        );
    }

    /**
     * CHeck if visitor is applicable to current facet builder.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return bool
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return true;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return string
     */
    public function visit(FacetBuilder $facetBuilder)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($facetBuilder)) {
                return $visitor->visit($facetBuilder);
            }
        }

        throw new NotImplementedException(
            'No visitor available for: ' . get_class($facetBuilder)
        );
    }
}
