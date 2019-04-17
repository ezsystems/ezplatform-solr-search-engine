<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\QueryConverter;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformSolrSearchEngine\Query\QueryConverter;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;

/**
 * Native implementation of Query Converter.
 */
class NativeQueryConverter extends QueryConverter
{
    /**
     * Query visitor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Sort clause visitor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * Facet builder visitor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor
     */
    protected $facetBuilderVisitor;

    /**
     * Construct from visitors.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $criterionVisitor
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor $sortClauseVisitor
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor $facetBuilderVisitor
     */
    public function __construct(
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        FacetFieldVisitor $facetBuilderVisitor
    ) {
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
    }

    public function convert(Query $query)
    {
        $params = array(
            /**
             * v7.7.0: 1. SOLR-11501: Starting a query string with local-params {!myparser ...} is used to switch the
             * query parser to another, and is intended for use by Solr system developers, not end users doing searches.
             * To reduce negative side-effects of unintended hack-ability, we've limited the cases that local-params
             * will be parsed to only contexts in which the default parser is "lucene" or "func". So if defType=edismax
             * then q={!myparser ...} won't work. In that example, put the desired query parser into defType. Another
             * example is if deftype=edismax then hl.q={!myparser ...} won't work for the same reason. In that example,
             * either put the desired query parser into hl.qparser or set hl.qparser=lucene. Most users won't run into
             * these cases but some will and must change. If you must have full backwards compatibility, use
             * luceneMatchVersion=7.1.0 or something earlier.
             */
            //'defType' => 'edismax',
            'q' => '{!lucene}' . $this->criterionVisitor->visit($query->query),
            'fq' => '{!lucene}' . $this->criterionVisitor->visit($query->filter),
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
     * This method uses spl_object_hash() to get id of each and every facet builder, as this
     * is expected by {@link \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor}.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return array
     */
    private function getFacetParams(array $facetBuilders)
    {
        $facetSets = array_map(
            function ($facetBuilder) {
                return $this->facetBuilderVisitor->visitBuilder($facetBuilder, spl_object_hash($facetBuilder));
            },
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
