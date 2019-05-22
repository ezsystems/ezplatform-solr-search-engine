<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy;

/**
 * Solr Cloud distributed search.
 *
 * @see https://lucene.apache.org/solr/guide/7_7/distributed-requests.html
 */
final class CloudDistributionStrategy extends AbstractDistributionStrategy
{
    private const COLLECTION_SEPARATOR = ',';
    private const COLLECTION_PARAMETER = 'collection';

    protected function appendSearchTargets(array $parameters, array $searchTargets): array
    {
        $collections = array_map(function(string $endpointName) {
            return $this->endpointRegistry->getEndpoint($endpointName)->core;
        }, $searchTargets);

        $parameters[self::COLLECTION_PARAMETER] = implode(self::COLLECTION_SEPARATOR, $collections);

        return $parameters;
    }
}
