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

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandlerInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * The Content Search handler retrieves sets of of Content objects, based on a
 * set of criteria.
 *
 * The basic idea of this class is to do the following:
 *
 * 1) The find methods retrieve a recursive set of filters, which define which
 * content objects to retrieve from the database. Those may be combined using
 * boolean operators.
 *
 * 2) This recursive criterion definition is visited into a query, which limits
 * the content retrieved from the database. We might not be able to create
 * sensible queries from all criterion definitions.
 *
 * 3) The query might be possible to optimize (remove empty statements),
 * reduce singular and and or constructs…
 *
 * 4) Additionally we might need a post-query filtering step, which filters
 * content objects based on criteria, which could not be converted in to
 * database statements.
 */
class Handler implements SearchHandlerInterface
{
    /**
     * Content locator gateway.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway
     */
    protected $gateway;

    /**
     * Content handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Document mapper.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper
     */
    protected $mapper;

    /**
     * Result extractor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor
     */
    protected $resultExtractor;

    /**
     * Core filter service.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\CoreFilter
     */
    protected $coreFilter;

    /**
     * Creates a new content handler.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway $gateway
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper $mapper
     * @param \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor $resultExtractor
     * @param \EzSystems\EzPlatformSolrSearchEngine\CoreFilter $coreFilter
     */
    public function __construct(
        Gateway $gateway,
        ContentHandler $contentHandler,
        DocumentMapper $mapper,
        ResultExtractor $resultExtractor,
        CoreFilter $coreFilter
    ) {
        $this->gateway = $gateway;
        $this->contentHandler = $contentHandler;
        $this->mapper = $mapper;
        $this->resultExtractor = $resultExtractor;
        $this->coreFilter = $coreFilter;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Query criterion is not applicable to its target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent(Query $query, array $fieldFilters = array())
    {
        $query = clone $query;
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        $this->coreFilter->apply(
            $query,
            $fieldFilters,
            DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_CONTENT
        );

        return $this->resultExtractor->extract(
            $this->gateway->findContent($query, $fieldFilters)
        );
    }

    /**
     * Performs a query for a single content object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function findSingle(Criterion $filter, array $fieldFilters = array())
    {
        $query = new Query();
        $query->filter = $filter;
        $query->query = new Criterion\MatchAll();
        $query->offset = 0;
        $query->limit = 1;

        $this->coreFilter->apply(
            $query,
            $fieldFilters,
            DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_CONTENT
        );

        $result = $this->resultExtractor->extract(
            $this->gateway->findContent($query, $fieldFilters)
        );

        if (!$result->totalCount) {
            throw new NotFoundException('Content', 'findSingle() found no content for given $filter');
        } elseif ($result->totalCount > 1) {
            throw new InvalidArgumentException('totalCount', 'findSingle() found more then one item for given $filter');
        }

        $first = reset($result->searchHits);

        return $first->valueObject;
    }

    /**
     * Finds Locations for the given $query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations(LocationQuery $query, array $fieldFilters = array())
    {
        $query = clone $query;
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        $this->coreFilter->apply(
            $query,
            $fieldFilters,
            DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_LOCATION
        );

        return $this->resultExtractor->extract(
            $this->gateway->findLocations($query, $fieldFilters)
        );
    }

    /**
     * Suggests a list of values for the given prefix.
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest($prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null)
    {
        throw new \Exception('@todo: Not implemented yet.');
    }

    /**
     * Indexes a content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function indexContent(Content $content)
    {
        $this->gateway->bulkIndexDocuments(array($this->mapper->mapContentBlock($content)));
    }

    /**
     * Indexes several content objects.
     *
     * Notes:
     * - Does not force a commit on solr, depends on solr config, use {@see commit()} if you need that.
     * - On large amounts of data make sure to iterate with several calls to this function with a limited
     *   set of content objects, amount you have memory for depends on server, size of objects, & PHP version.
     *
     * @todo: This method, {@see purgeIndex}, & {@see commit()} is needed for being able to bulk index content.
     *       However it is not added to an official SPI interface yet as we anticipate adding a bulkIndexDocument
     *       using eZ\Publish\SPI\Search\Document instead of bulkIndexContent based on Content objects. However
     *       that won't be added until we have several stable or close to stable advance search engines to make
     *       sure we match the features of these. 
     *       See also {@see Solr\Content\Search\Gateway\Native::bulkIndexContent} for further Solr specific info.
     *
     * @param \eZ\Publish\SPI\Persistence\Content[] $contentObjects
     */
    public function bulkIndexContent(array $contentObjects)
    {
        $documents = array();

        foreach ($contentObjects as $content) {
            $documents[] = $this->mapper->mapContentBlock($content);
        }

        if (!empty($documents)) {
            $this->gateway->bulkIndexDocuments($documents);
        }
    }

    /**
     * Indexes a Location in the index storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation(Location $location)
    {
        // Does nothing: in this implementation Locations are indexed as
        //               a part of Content document block
    }

    /**
     * Deletes a content object from the index.
     *
     * @param int $contentId
     * @param int|null $versionId
     */
    public function deleteContent($contentId, $versionId = null)
    {
        $idPrefix = $this->mapper->generateContentDocumentId($contentId);

        $this->gateway->deleteByQuery("_root_:{$idPrefix}*");
    }

    /**
     * Deletes a location from the index.
     *
     * @param mixed $locationId
     * @param mixed $contentId
     */
    public function deleteLocation($locationId, $contentId)
    {
        $idPrefix = $this->mapper->generateContentDocumentId($contentId);

        $this->gateway->deleteByQuery("_root_:{$idPrefix}*");

        // TODO it seems this part of location deletion (not last location) misses integration tests
        try {
            $contentInfo = $this->contentHandler->loadContentInfo($contentId);
        } catch (NotFoundException $e) {
            return;
        }

        $content = $this->contentHandler->load($contentId, $contentInfo->currentVersionNo);
        $this->bulkIndexContent(array($content));
    }

    /**
     * Purges all contents from the index.
     *
     * @see bulkIndexContent() For info on why this is not on an SPI Interface yet.
     */
    public function purgeIndex()
    {
        $this->gateway->purgeIndex();
    }

    /**
     * Commits the data to the Solr index, making it available for search.
     *
     * This will perform Solr 'soft commit', which means there is no guarantee that data
     * is actually written to the stable storage, it is only made available for search.
     * Passing true will also write the data to the safe storage, ensuring durability.
     *
     * @see bulkIndexContent() For info on why this is not on an SPI Interface yet.
     * 
     * @param bool $flush
     */
    public function commit($flush = false)
    {
        $this->gateway->commit($flush);
    }
}
