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

use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;

/**
 * Abstract implementation of Search Extractor, which extracts search result
 * from the data returned by Solr backend.
 */
abstract class ResultExtractor
{
    /**
     * Facet builder visitor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor
     */
    protected $facetBuilderVisitor;

    public function __construct(FacetFieldVisitor $facetBuilderVisitor)
    {
        $this->facetBuilderVisitor = $facetBuilderVisitor;
    }

    /**
     * Extracts search result from $data returned by Solr backend.
     *
     * @param mixed $data
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
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
            // We'll first need to generate id's for facet builders to match against fields, as also done for
            // visit stage in NativeQueryConverter.
            $facetBuildersById = [];
            foreach ($facetBuilders as $facetBuilder) {
                $facetBuildersById[spl_object_hash($facetBuilder)] = $facetBuilder;
            }

            foreach ($data->facet_counts as $facetCounts) {
                foreach ($facetCounts as $field => $facet) {
                    if (empty($facetBuildersById[$field])) {
                        @trigger_error(
                            'Not setting id of field using FacetFieldVisitor::visitBuilder will not be supported in 2.0'
                            . ', as it makes it impossible to exactly identify which facets belongs to which builder.'
                            . "\nMake sure to adapt your visitor for the following field: ${field}"
                            . "\nExample: 'facet.field' => \"{!ex=dt key=\${id}}${field}\",",
                            E_USER_DEPRECATED);
                    }

                    $result->facets[] = $this->facetBuilderVisitor->mapField(
                        $field,
                        (array)$facet,
                        isset($facetBuildersById[$field]) ? $facetBuildersById[$field] : null
                    );
                }
            }
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
