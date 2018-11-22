<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use RuntimeException;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
class SolrCloud extends Native
{
    /**
     * Returns search hits for the given query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $languageSettings - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return mixed
     */
    public function findContent(Query $query, array $languageSettings = array())
    {
        $parameters = $this->contentQueryConverter->convert($query);

        return $this->internalFind($parameters, $languageSettings);
    }

    /**
     * Returns search hits for the given array of Solr query parameters.
     *
     * @param array $parameters
     * @param array $languageSettings - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return mixed
     */
    protected function internalFind(array $parameters, array $languageSettings = array())
    {
        $shards = $this->endpointResolver->getSearchTargets($languageSettings);

        if (!empty($shards)) {
            $parameters['shards'] = implode(',', $shards);
        }

        return $this->search($parameters);
    }

    public function searchAllEndpoints(Query $query)
    {
        $parameters = $this->contentQueryConverter->convert($query);

        return $this->search($parameters);
    }

    /**
     * Only return endpoints if there are more then one configured, as this is meant for use on shard parameter.
     *
     if ($this->endpointResolver instanceof SingleEndpointResolver && !$this->endpointResolver->hasMultipleEndpoints()) {
         return '';
     }

     * Only return endpoints if there are more then one configured, as this is meant for use on shard parameter.
     *
     if ($this->endpointResolver instanceof SingleEndpointResolver && !$this->endpointResolver->hasMultipleEndpoints()) {
         return '';
     }

     * Indexes an array of documents.
     *
     * Documents are given as an array of the array of documents. The array of documents
     * holds documents for all translations of the particular entity.
     *
     * Notes:
     * - Does not force a commit on solr, depends on solr config, use {@commit} if you need that.
     * - On large amounts of data make sure to iterate with several calls to this function with a limited
     *   set of documents, amount you have memory for depends on server, size of documents, & PHP version.
     *
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     */
    public function bulkIndexDocuments(array $documents)
    {
        $routedDocuments = [];
        $mainTranslationsShardId = $this->endpointResolver->getMainLanguagesEndpoint();

        foreach ($documents as $document) {
            $shardId = $this->endpointResolver->getIndexingTarget($document->languageCode);
            $document2 = clone $document;
            $this->routeDocument($document2, $shardId);
            $routedDocuments[] = $document2;

            if ($mainTranslationsShardId !== null && $document->isMainTranslation) {
                $mainTranslationsDocument = $this->getMainTranslationDocument($document);
                $this->routeDocument($mainTranslationsDocument, $mainTranslationsShardId);
                $routedDocuments[] = $mainTranslationsDocument;
            }
        }

        $this->doBulkIndexDocuments(
            $this->endpointRegistry->getEndpoint(
                $this->endpointResolver->getEntryEndpoint()
            ),
            $routedDocuments
        );
    }

    /**
     * Routes a given $document to the given $shardId.
     *
     * Adds a special field, recognized by the implicit router.
     * Note that collection must be created to support this type of routing.
     *
     * @param \eZ\Publish\SPI\Search\Document $document
     * @param string $shardId
     */
    private function routeDocument(Document $document, $shardId)
    {
        $document->fields[] = new Field(
            'router_field',
            $shardId,
            new FieldType\IdentifierField()
        );
    }

    /**
     * Deletes documents by the given $query.
     *
     * @param string $query
     */
    public function deleteByQuery($query)
    {
        $this->client->request(
            'POST',
            $this->endpointRegistry->getEndpoint(
                $this->endpointResolver->getEntryEndpoint()
            ),
            '/update?wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                "<delete><query>{$query}</query></delete>"
            )
        );
    }

    /**
     * @todo implement purging for document type
     *
     * Purges all contents from the index
     */
    public function purgeIndex()
    {
        $this->client->request(
            'POST',
            $this->endpointRegistry->getEndpoint(
                $this->endpointResolver->getEntryEndpoint()
            ),
            '/update?wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                '<delete><query>*:*</query></delete>'
            )
        );
    }

    /**
     * Commits the data to the Solr index, making it available for search.
     *
     * This will perform Solr 'soft commit', which means there is no guarantee that data
     * is actually written to the stable storage, it is only made available for search.
     * Passing true will also write the data to the safe storage, ensuring durability.
     *
     * @param bool $flush
     */
    public function commit($flush = false)
    {
        $payload = $flush ?
            '<commit/>' :
            '<commit softCommit="true"/>';

        $result = $this->client->request(
            'POST',
            $this->endpointRegistry->getEndpoint(
                $this->endpointResolver->getEntryEndpoint()
            ),
            '/update',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                $payload
            )
        );

        if ($result->headers['status'] !== 200) {
            throw new RuntimeException(
                'Wrong HTTP status received from Solr: ' .
                $result->headers['status'] . var_export($result, true)
            );
        }
    }
}
