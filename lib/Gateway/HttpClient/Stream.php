<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Message;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint;

/**
 * Simple PHP stream based HTTP client.
 */
class Stream implements HttpClient
{
    /**
     * @var int
     */
    private $connectionTimeout;

    /**
     * @var int
     */
    private $connectionRetry;

    /**
     * @var int
     */
    private $retryWaitMs;

    /**
     * Stream constructor.
     *
     * @param int $connectionTimeout Timeout for connection in seconds.
     * @param int $connectionRetry Number of times to re-try connection.
     * @param int $retryWaitMs Time in milli seconds.
     */
    public function __construct($connectionTimeout = 10, $connectionRetry = 5, $retryWaitMs = 100)
    {
        $this->connectionTimeout = $connectionTimeout;
        $this->connectionRetry = $connectionRetry;
        $this->retryWaitMs = $retryWaitMs;
    }

    /**
     * Execute a HTTP request to the remote server.
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint $endpoint
     * @param string $path
     * @param Message $message
     *
     * @return Message
     */
    public function request($method, Endpoint $endpoint, $path, Message $message = null)
    {
        $message = $message ?: new Message();

        // We'll try to reach backend 5 times before throwing exception.
        $i = 0;
        do {
            ++$i;
            if ($responseMessage = $this->requestStream($method, $endpoint, $path, $message)) {
                return $responseMessage;
            }

            // Wait for 100ms before we retry
            // Timeout is 10s, so time spent in worst case is 50.5s, which is less then default_socket_timeout (60s)
            usleep($this->retryWaitMs*1000);
        } while ($i < $this->connectionRetry);

        throw new ConnectionException($endpoint->getURL(), $path, $method);
    }

    private function requestStream($method, Endpoint $endpoint, $path, Message $message)
    {
        $requestHeaders = $this->getRequestHeaders($message, $endpoint);
        $contextOptions = array(
            'http' => array(
                'method' => $method,
                'content' => $message->body,
                'ignore_errors' => true,
                'timeout' => $this->connectionTimeout,
                'header' => $requestHeaders,
            ),
        );

        $httpFilePointer = @fopen(
            $endpoint->getURL() . $path,
            'r',
            false,
            stream_context_create($contextOptions)
        );

        // Check if connection has been established successfully
        if ($httpFilePointer === false) {
            return null;
        }

        // Read request body
        $body = '';
        while (!feof($httpFilePointer)) {
            $body .= fgets($httpFilePointer);
        }

        $metaData = stream_get_meta_data($httpFilePointer);
        // This depends on PHP compiled with or without --curl-enable-streamwrappers
        $rawHeaders = isset($metaData['wrapper_data']['headers']) ?
            $metaData['wrapper_data']['headers'] :
            $metaData['wrapper_data'];
        $headers = array();

        foreach ($rawHeaders as $lineContent) {
            // Extract header values
            if (preg_match('(^HTTP/(?P<version>\d+\.\d+)\s+(?P<status>\d+))S', $lineContent, $match)) {
                $headers['version'] = $match['version'];
                $headers['status'] = (int)$match['status'];
            } else {
                list($key, $value) = explode(':', $lineContent, 2);
                $headers[$key] = ltrim($value);
            }
        }

        return new Message(
            $headers,
            $body
        );
    }

    /**
     * Get formatted request headers.
     *
     * Merged with the default values.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\Message $message
     * @param \EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint $endpoint
     *
     * @return string
     */
    protected function getRequestHeaders(Message $message, Endpoint $endpoint)
    {
        // Use message headers as default
        $headers = $message->headers;

        // Set headers from $endpoint
        if ($endpoint->user !== null) {
            $headers['Authorization'] = 'Basic ' . base64_encode("{$endpoint->user}:{$endpoint->pass}");
        }

        // Render headers
        $requestHeaders = '';

        foreach ($headers as $name => $value) {
            if (is_numeric($name)) {
                throw new \RuntimeException("Invalid HTTP header name $name");
            }

            $requestHeaders .= "$name: $value\r\n";
        }

        return $requestHeaders;
    }
}
