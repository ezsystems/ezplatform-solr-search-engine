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
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;

/**
 * Standalone setup of distributed search.
 *
 * @see https://lucene.apache.org/solr/guide/7_7/distributed-search-with-index-sharding.html
 */
final class StandaloneDistributionStrategy implements DistributionStrategy
{
    /**
     * Endpoint registry service.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry
     */
    private $endpointRegistry;

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

    public function getSearchTargets(array $endpoints): array
    {
        return array_map(function (string $name) {
            return $this->endpointRegistry->getEndpoint($name)->getIdentifier();
        }, $endpoints);
    }

    public function getDocumentRouter(): DocumentRouter
    {
        return $this->documentRouter;
    }
}
