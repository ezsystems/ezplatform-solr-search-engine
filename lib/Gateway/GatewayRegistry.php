<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway;

use EzSystems\EzPlatformSolrSearchEngine\Gateway;
use OutOfBoundsException;

/**
 * Registry for Solr search engine coreFilters.
 */
final class GatewayRegistry
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Gateway[]
     */
    protected $gateways;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway[] $gateways
     */
    public function __construct(array $gateways = [])
    {
        $this->gateways = $gateways;
    }

    public function addGateway(string $connectionName, Gateway $gateway): void
    {
        $this->gateways[$connectionName] = $gateway;
    }

    public function getGateway(string $connectionName): Gateway
    {
        if (!isset($this->gateways[$connectionName])) {
            throw new OutOfBoundsException(sprintf('No Gateway registered for connection \'%s\'', $connectionName));
        }

        return $this->gateways[$connectionName];
    }

    public function hasGateway(string $connectionName): bool
    {
        return isset($this->gateways[$connectionName]);
    }

    public function setGateways(array $gateways): void
    {
        $this->gateways = $gateways;
    }
}
