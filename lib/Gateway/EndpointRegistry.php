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

use OutOfBoundsException;

/**
 * Registry for Solr search engine Endpoints.
 */
class EndpointRegistry
{
    /**
     * Registered endpoints.
     *
     * @var array(string => Endpoint)
     */
    protected $endpoint = [];

    /**
     * Construct from optional array of Endpoints.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint[] $endpoints
     */
    public function __construct(array $endpoints = [])
    {
        foreach ($endpoints as $name => $endpoint) {
            $this->registerEndpoint($name, $endpoint);
        }
    }

    /**
     * Registers $endpoint with $name.
     *
     * @param string $name
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint $endpoint
     */
    public function registerEndpoint($name, Endpoint $endpoint)
    {
        $this->endpoint[$name] = $endpoint;
    }

    /**
     * Get Endpoint with $name.
     *
     * @param string $name
     *
     * @return \EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint
     */
    public function getEndpoint($name)
    {
        if (!isset($this->endpoint[$name])) {
            throw new OutOfBoundsException("No Endpoint registered for '{$name}'.");
        }

        return $this->endpoint[$name];
    }

    /**
     * Get first Endpoint, for usecases where there is only one.
     *
     * @return \EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint
     */
    public function getFirstEndpoint()
    {
        if (empty($this->endpoint)) {
            throw new OutOfBoundsException("No Endpoint registered at all'.");
        }

        return reset($this->endpoint);
    }
}
