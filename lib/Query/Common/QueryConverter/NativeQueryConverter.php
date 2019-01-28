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
            'defType' => 'edismax',
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

        if ($query->query instanceof Query\Criterion\FullText) {
            // https://lucene.apache.org/solr/guide/6_6/highlighting.html
            $params['hl'] = 'on';
            $params['hl.fl'] = 'meta_content__text_t';
            // @deprecated @todo Enable once we drop Solr 4.4 support
            //$params['hl.method'] = 'unified';
            // @todo Should this be somehow configurable? And should we take boost fields into account?

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
