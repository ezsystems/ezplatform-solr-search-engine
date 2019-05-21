<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\DocumentRouter\NullDocumentRouter;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\SingleEndpointResolver;

/**
 * Standalone setup of distributed search.
 *
 * @see https://lucene.apache.org/solr/guide/7_7/distributed-search-with-index-sharding.html
 */
final class StandaloneDistributionStrategy implements DistributionStrategy
{
    private const SHARD_SEPARATOR = ',';

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
     */
    public function __construct(EndpointRegistry $endpointRegistry)
    {
        $this->endpointRegistry = $endpointRegistry;
        $this->documentRouter = new NullDocumentRouter();
    }

    public function getDocumentRouter(): DocumentRouter
    {
        return $this->documentRouter;
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
            $shards = array_map(function(Endpoint $endpoint) {
                return $endpoint->getIdentifier();
            }, $searchTargets);

            $parameters['shards'] = implode(self::SHARD_SEPARATOR, $shards);
        }

        return $parameters;
    }
}
