<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\DocumentRouter;

use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointReference;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;

/**
 * Implicit document routing strategy.
 *
 * @see https://lucene.apache.org/solr/guide/6_6/shards-and-indexing-data-in-solrcloud.html#ShardsandIndexingDatainSolrCloud-DocumentRouting
 */
final class ImplicitDocumentRouter implements DocumentRouter
{
    /**
     * Endpoint registry service.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver
     */
    private $endpointResolver;

    /**
     * @var string
     */
    private $routerFieldName;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver $endpointResolver
     * @param string $routerFieldName
     */
    public function __construct(EndpointResolver $endpointResolver, string $routerFieldName = 'router_field')
    {
        $this->endpointResolver = $endpointResolver;
        $this->routerFieldName = $routerFieldName;
    }

    public function processDocument(Document $document): Document
    {
        $endpoint = EndpointReference::fromString(
            $this->endpointResolver->getIndexingTarget($document->languageCode)
        );

        return $this->addRouterField($document, $endpoint);
    }

    public function processMainTranslationDocument(Document $document): Document
    {
        $endpoint = EndpointReference::fromString(
            $this->endpointResolver->getMainLanguagesEndpoint()
        );

        return $this->addRouterField($document, $endpoint);
    }

    private function addRouterField(Document $document, EndpointReference $endpoint): Document
    {
        $document = clone $document;
        $document->fields[] = new Field(
            $this->routerFieldName,
            $endpoint->shard,
            new FieldType\IdentifierField()
        );

        return $document;
    }
}
