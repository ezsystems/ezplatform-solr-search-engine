<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Message;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;

/**
 * Simple PHP stream based HTTP client.
 *
 * @internal type-hint {@see \EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient} instead.
 */
class Stream implements HttpClient, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * @param int $timeout Timeout for connection in seconds.
     * @param int $retry Number of times to re-try connection.
     * @param int $retryWaitMs Time in milliseconds.
     */
    public function __construct(
        int $timeout = 10,
        int $retry = 5,
        int $retryWaitMs = 100
    ) {
        $this->setLogger(new NullLogger());
        $this->connectionTimeout = $timeout;
        $this->connectionRetry = $retry;
        $this->retryWaitMs = $retryWaitMs;
    }

    /**
     * Execute a HTTP request to the remote server.
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param string $path
     *
     * @return Message
     */
    public function request($method, Endpoint $endpoint, $path, Message $message = null)
    {
        $message = $message ?: new Message();

        // We'll try to reach backend several times before throwing exception.
        $i = 0;
        do {
            ++$i;
            if ($responseMessage = $this->requestStream($method, $endpoint, $path, $message)) {
                return $responseMessage;
            }

            usleep($this->retryWaitMs * 1000);

            $this->logger->warning(
                sprintf(
                    'Connection attempt #%d to %s failed, retrying after %d ms',
                    $i,
                    $endpoint->getURL(),
                    $this->retryWaitMs
                )
            );
        } while ($i < $this->connectionRetry);

        $this->logger->error(
            sprintf(
                'Connection to %s failed, attempted %d times',
                $endpoint->getURL(),
                $this->connectionRetry
            )
        );

        throw new ConnectionException($endpoint->getURL(), $path, $method);
    }

    private function requestStream($method, Endpoint $endpoint, $path, Message $message)
    {
        $requestHeaders = $this->getRequestHeaders($message, $endpoint);
        $contextOptions = [
            'http' => [
                'method' => $method,
                'content' => $message->body,
                'ignore_errors' => true,
                'timeout' => $this->connectionTimeout,
                'header' => $requestHeaders,
            ],
        ];

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
        $headers = [];

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
