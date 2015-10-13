<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\SolrSearchEngine\Query\Common\QueryConverter;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\SolrSearchEngine\Query\QueryConverter;
use EzSystems\SolrSearchEngine\Query\CriterionVisitor;
use EzSystems\SolrSearchEngine\Query\SortClauseVisitor;
use EzSystems\SolrSearchEngine\Query\FacetBuilderVisitor;

/**
 * Native implementation of Query Converter.
 */
class NativeQueryConverter extends QueryConverter
{
    /**
     * Query visitor.
     *
     * @var \EzSystems\SolrSearchEngine\Query\CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Sort clause visitor.
     *
     * @var \EzSystems\SolrSearchEngine\Query\SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * Facet builder visitor.
     *
     * @var \EzSystems\SolrSearchEngine\Query\FacetBuilderVisitor
     */
    protected $facetBuilderVisitor;

    /**
     * Construct from visitors.
     *
     * @param \EzSystems\SolrSearchEngine\Query\CriterionVisitor $criterionVisitor
     * @param \EzSystems\SolrSearchEngine\Query\SortClauseVisitor $sortClauseVisitor
     * @param \EzSystems\SolrSearchEngine\Query\FacetBuilderVisitor $facetBuilderVisitor
     */
    public function __construct(
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        FacetBuilderVisitor $facetBuilderVisitor
    ) {
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
    }

    public function convert(Query $query)
    {
        $params = array(
            'defType' => 'edismax',
            'q.alt' => $this->criterionVisitor->visit($query->query),
            'fq' => $this->criterionVisitor->visit($query->filter),
            'sort' => $this->getSortClauses($query->sortClauses),
            'start' => $query->offset,
            'rows' => $query->limit,
            'fl' => '*,score,[shard]',
            'wt' => 'json',
        );

        $facetParams = $this->getFacetParams($query->facetBuilders);
        if (!empty($facetParams)) {
            $params['facet'] = 'true';
            $params['facet.sort'] = 'count';
            $params = array_merge($facetParams, $params);
        }

        return $params;
    }

    /**
     * Converts an array of sort clause objects to a proper Solr representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @return string
     */
    private function getSortClauses(array $sortClauses)
    {
        return implode(
            ', ',
            array_map(
                array($this->sortClauseVisitor, 'visit'),
                $sortClauses
            )
        );
    }

    /**
     * Converts an array of facet builder objects to a Solr query parameters representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return array
     */
    private function getFacetParams(array $facetBuilders)
    {
        $facetSets = array_map(
            array($this->facetBuilderVisitor, 'visit'),
            $facetBuilders
        );

        $facetParams = array();

        // In case when facet sets contain same keys, merge them in an array
        foreach ($facetSets as $facetSet) {
            foreach ($facetSet as $key => $value) {
                if (isset($facetParams[$key])) {
                    if (!is_array($facetParams[$key])) {
                        $facetParams[$key] = array($facetParams[$key]);
                    }
                    $facetParams[$key][] = $value;
                } else {
                    $facetParams[$key] = $value;
                }
            }
        }

        return $facetParams;
    }
}
