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

use eZ\Publish\API\Repository\Values\Content\Search\AggregationResultCollection;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use stdClass;

/**
 * Abstract implementation of Search Extractor, which extracts search result
 * from the data returned by Solr backend.
 */
abstract class ResultExtractor
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor */
    protected $facetBuilderVisitor;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor */
    protected $aggregationResultExtractor;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry */
    protected $endpointRegistry;

    public function __construct(
        FacetFieldVisitor $facetBuilderVisitor,
        AggregationResultExtractor $aggregationResultExtractor,
        EndpointRegistry $endpointRegistry
    ) {
        $this->facetBuilderVisitor = $facetBuilderVisitor;
        $this->aggregationResultExtractor = $aggregationResultExtractor;
        $this->endpointRegistry = $endpointRegistry;
    }

    /**
     * Extracts search result from $data returned by Solr backend.
     *
     * @param mixed $data
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation[] $aggregations
     * @param array $languageFilter
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function extract($data, array $facetBuilders = [], array $aggregations = [], array $languageFilter = [])
    {
        $result = new SearchResult(
            [
                'time' => $data->responseHeader->QTime / 1000,
                'maxScore' => $data->response->maxScore,
                'totalCount' => $data->response->numFound,
            ]
        );

        $result->facets = $this->extractFacets($data, $facetBuilders, $languageFilter);
        $result->aggregations = $this->extractAggregations($data, $aggregations, $languageFilter);

        foreach ($data->response->docs as $doc) {
            $result->searchHits[] = $this->extractSearchHit($doc, $languageFilter);
        }

        return $result;
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
        // In single core setup, shard parameter is not set on request to avoid issues in environments that does not
        // know about own dns, which means it's not set here either
        if ($hit->{'[shard]'} === '[not a shard request]') {
            return $this->endpointRegistry->getFirstEndpoint()->getIdentifier();
        }

        return $hit->{'[shard]'};
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation[] $aggregations
     */
    protected function extractAggregations(
        stdClass $data,
        array $aggregations,
        array $languageFilter
    ): AggregationResultCollection {
        $aggregationsResults = [];
        foreach ($aggregations as $aggregation) {
            $name = $aggregation->getName();

            if (isset($data->facets->{$name})) {
                $aggregationsResults[] = $this->aggregationResultExtractor->extract(
                    $aggregation,
                    $languageFilter,
                    $data->facets->{$name}
                );
            }
        }

        return new AggregationResultCollection($aggregationsResults);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     */
    protected function extractFacets(stdClass $data, array $facetBuilders, array $languageFilter): array
    {
        $facets = [];

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
                            'Not setting id of field using FacetFieldVisitor::visitBuilder will not be supported in 4.0'
                            . ', as it makes it impossible to exactly identify which facets belongs to which builder.'
                            . "\nMake sure to adapt your visitor for the following field: ${field}"
                            . "\nExample: 'facet.field' => \"{!ex=dt key=\${id}}${field}\",",
                            E_USER_DEPRECATED);
                    }

                    $facets[] = $this->facetBuilderVisitor->mapField(
                        $field,
                        (array)$facet,
                        $facetBuildersById[$field] ?? null
                    );
                }
            }
        }

        return $facets;
    }

    protected function extractSearchHit(stdClass $doc, array $languageFilter): SearchHit
    {
        return new SearchHit(
            [
                'score' => $doc->score,
                'index' => $this->getIndexIdentifier($doc),
                'matchedTranslation' => $this->getMatchedLanguageCode($doc),
                'valueObject' => $this->extractHit($doc),
            ]
        );
    }
}
