<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\SingleEndpointResolver;

abstract class AbstractDistributionStrategy implements DistributionStrategy
{
    /**
     * Endpoint registry service.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry
     */
    protected $endpointRegistry;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver
     */
    protected $endpointResolver;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry $endpointRegistry
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver $endpointResolver
     */
    public function __construct(EndpointRegistry $endpointRegistry, EndpointResolver $endpointResolver)
    {
        $this->endpointRegistry = $endpointRegistry;
        $this->endpointResolver = $endpointResolver;
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
            $parameters = $this->appendSearchTargets($parameters, $searchTargets);
        }

        return $parameters;
    }

    protected abstract function appendSearchTargets(array $parameters, array $searchTargets): array;
}
