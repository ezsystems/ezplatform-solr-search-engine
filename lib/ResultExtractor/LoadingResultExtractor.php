<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor;

use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor;
use RuntimeException;

/**
 * The Loading Result Extractor extracts the value object from the Solr search hit data
 * by loading it from the persistence.
 */
class LoadingResultExtractor extends ResultExtractor
{
    /**
     * Content handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Location handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    public function __construct(
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        FacetBuilderVisitor $facetBuilderVisitor,
        EndpointRegistry $endpointRegistry
    ) {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;

        parent::__construct($facetBuilderVisitor, $endpointRegistry);
    }

    /**
     * Extracts value object from $hit returned by Solr backend.
     *
     * @throws \RuntimeException If search $hit could not be handled
     *
     * @param mixed $hit
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function extractHit($hit)
    {
        if ($hit->document_type_id === 'content') {
            return $this->contentHandler->loadContentInfo($hit->content_id_id);
        }

        if ($hit->document_type_id === 'location') {
            return $this->locationHandler->load($hit->location_id_id);
        }

        throw new RuntimeException("Could not extract: document of type '{$hit->document_type_id}' is not handled.");
    }
}
