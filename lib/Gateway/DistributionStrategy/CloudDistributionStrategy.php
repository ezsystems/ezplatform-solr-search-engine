<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointReference;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;

/**
 * Solr Cloud distributed search.
 *
 * @see https://lucene.apache.org/solr/guide/7_7/distributed-requests.html
 */
final class CloudDistributionStrategy implements DistributionStrategy
{
    /**
     * Endpoint registry service.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver
     */
    private $endpointResolver;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter
     */
    private $documentRouter;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver $endpointResolver
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter $documentRouter
     */
    public function __construct(EndpointResolver $endpointResolver, DocumentRouter $documentRouter)
    {
        $this->endpointResolver = $endpointResolver;
        $this->documentRouter = $documentRouter;
    }

    public function getSearchTargets(array $endpoints): array
    {
        $entryEndpoint = $this->endpointResolver->getEntryEndpoint();

        return array_map(function ($name) use ($entryEndpoint) {
            $reference = EndpointReference::fromString($name);

            if ($reference->endpoint !== $entryEndpoint) {
                throw new \RuntimeException('Multiple entry endpoint are not supported by Solr Cloud');
            }

            return $reference->shard;
        }, $endpoints);
    }

    public function getDocumentRouter(): DocumentRouter
    {
        return $this->documentRouter;
    }
}
