<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine;

use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;

/**
 * Abstract implementation of Search Extractor, which extracts search result
 * from the data returned by Solr backend.
 */
abstract class ResultExtractor
{
    /**
     * Facet builder visitor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor
     */
    protected $facetBuilderVisitor;

    public function __construct(FacetBuilderVisitor $facetBuilderVisitor)
    {
        $this->facetBuilderVisitor = $facetBuilderVisitor;
    }

    /**
     * Extracts search result from $data returned by Solr backend.
     *
     * @param mixed $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function extract($data, array $facetBuilders = [])
    {
        $result = new SearchResult(
            array(
                'time' => $data->responseHeader->QTime / 1000,
                'maxScore' => $data->response->maxScore,
                'totalCount' => $data->response->numFound,
            )
        );

        if (isset($data->facet_counts)) {
            $result->facets = $this->extractFacets($data->facet_counts, $facetBuilders);
        }

        foreach ($data->response->docs as $doc) {
            $searchHit = new SearchHit(
                array(
                    'score' => $doc->score,
                    'index' => $this->getIndexIdentifier($doc),
                    'matchedTranslation' => $this->getMatchedLanguageCode($doc),
                    'valueObject' => $this->extractHit($doc),
                )
            );
            $result->searchHits[] = $searchHit;
        }

        return $result;
    }

    /**
     * Extract facets.
     *
     * @param array $facetCounts
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return array
     */
    private function extractFacets($facetCounts, array $facetBuilders)
    {
        $facets = [];
        if (!$this->facetBuilderVisitor instanceof FacetFieldVisitor) {
            // @deprecated logic
            foreach ($facetCounts as $facetList) {
                foreach ($facetList as $field => $facet) {
                    $facets[] = $this->facetBuilderVisitor->map(
                        $field,
                        (array)$facet
                    );
                }
            }
        }

        foreach ($facetCounts as $facetList) {
            foreach ($facetList as $field => $facet) {
                $visitor = $this->facetBuilderVisitor->getFieldVisitor($field);

                foreach ($facetBuilders as $facetBuilder) {
                    if ($visitor->canMapField($field, $facetBuilder)) {
                        $facets[] = $visitor->mapField(
                            $field,
                            (array)$facet,
                            $facetBuilder
                        );
                    }
                }
            }
        }

        return $facets;
    }

    /**
     * Returns language code of the Content's translation of the matched document.
     *
     * @param $hit
     *
     * @return string
     */
    protected function getMatchedLanguageCode($hit)
    {
        return $hit->meta_indexed_language_code_s;
    }

    /**
     * Returns the identifier of the logical index (shard) of the matched document.
     *
     * @param mixed $hit
     *
     * @return string
     */
    protected function getIndexIdentifier($hit)
    {
        return $hit->{'[shard]'};
    }

    /**
     * Extracts value object from $hit returned by Solr backend.
     *
     * Needs to be implemented by the concrete ResultExtractor.
     *
     * @param mixed $hit
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    abstract public function extractHit($hit);
}
