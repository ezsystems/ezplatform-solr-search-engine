<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointReference;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\SingleEndpointResolver;

/**
 * Solr Cloud distributed search.
 *
 * @see https://lucene.apache.org/solr/guide/7_7/distributed-requests.html
 */
final class CloudDistributionStrategy implements DistributionStrategy
{
    private const COLLECTION_SEPARATOR = ',';

    /**
     * Endpoint registry service.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry
     */
    private $endpointRegistry;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver
     */
    private $endpointResolver;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter
     */
    private $documentRouter;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry $endpointRegistry
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver $endpointResolver
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter $documentRouter
     */
    public function __construct(EndpointRegistry $endpointRegistry, EndpointResolver $endpointResolver, DocumentRouter $documentRouter)
    {
        $this->endpointRegistry = $endpointRegistry;
        $this->endpointResolver = $endpointResolver;
        $this->documentRouter = $documentRouter;
    }

    public function getSearchParameters(array $parameters, ?array $languageSettings = null): array
    {
        if ($this->endpointResolver instanceof SingleEndpointResolver && !$this->endpointResolver->hasMultipleEndpoints()) {
            return $parameters;
        }

        $searchTargets = $languageSettings !== null ?
            $this->endpointResolver->getSearchTargets($languageSettings) :
            $this->endpointResolver->getEndpoints();

        if (!empty($searchTargets)) {
            $collections = array_map(function(string $endpointName) {
                return $this->endpointRegistry->getEndpoint($endpointName)->core;
            }, $searchTargets);

            $parameters['collection'] = implode(self::COLLECTION_SEPARATOR, $collections);
        }

        return $parameters;
    }

    public function getDocumentRouter(): DocumentRouter
    {
        return $this->documentRouter;
    }
}
