<?php

/**
 * File containing the HttpClient interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\SolrSearchEngine\Gateway;

/**
 * Interface for Http Client implementations.
 */
interface HttpClient
{
    /**
     * Execute a HTTP request to the remote server.
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param \EzSystems\SolrSearchEngine\Gateway\Endpoint $endpoint
     * @param string $path
     * @param \EzSystems\SolrSearchEngine\Gateway\Message $message
     *
     * @return \EzSystems\SolrSearchEngine\Gateway\Message
     */
    public function request($method, Endpoint $endpoint, $path, Message $message = null);
}
