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
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\QueryConverter;
use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;

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
        $params = [
            'q' => '{!lucene}' . $this->criterionVisitor->visit($query->query),
            'fq' => '{!lucene}' . $this->criterionVisitor->visit($query->filter),
            'sort' => $this->getSortClauses($query->sortClauses),
            'start' => $query->offset,
            'rows' => $query->limit,
            'fl' => '*,score,[shard]',
            'wt' => 'json',
        ];

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
                [$this->sortClauseVisitor, 'visit'],
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

        $facetParams = [];

        // In case when facet sets contain same keys, merge them in an array
        foreach ($facetSets as $facetSet) {
            foreach ($facetSet as $key => $value) {
                if (isset($facetParams[$key])) {
                    if (!\is_array($facetParams[$key])) {
                        $facetParams[$key] = [$facetParams[$key]];
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
