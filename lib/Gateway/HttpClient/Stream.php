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
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Simple PHP stream based HTTP client.
 *
 * @internal type-hint {@see \EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient} instead.
 */
class Stream implements HttpClient, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var int */
    private $connectionTimeout;

    /** @var \Symfony\Contracts\HttpClient\HttpClientInterface */
    private $client;

    /**
     * @param int $timeout Timeout for connection in seconds.
     */
    public function __construct(HttpClientInterface $client, int $timeout = 10)
    {
        $this->client = $client;
        $this->connectionTimeout = $timeout;
        $this->setLogger(new NullLogger());
    }

    /**
     * Execute an HTTP request to the remote server.
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param string $path
     *
     * @throws \EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient\ConnectionException
     */
    public function request($method, Endpoint $endpoint, $path, Message $message = null): Message
    {
        $message = $message ?? new Message();

        try {
            return $this->getResponseMessage(
                $method,
                $endpoint,
                $path,
                $message
            );
        } catch (ExceptionInterface $e) {
            throw new ConnectionException($endpoint->getURL(), $path, $method, $e);
        }
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function getResponseMessage(
        $method,
        Endpoint $endpoint,
        $path,
        Message $message
    ): Message {
        if ($endpoint->user !== null) {
            $headers['Authorization'] = 'Basic ' . base64_encode("{$endpoint->user}:{$endpoint->pass}");
        }

        $response = $this->client->request(
            $method,
            $endpoint->getURL() . $path,
            [
                'headers' => $message->headers,
                'timeout' => $this->connectionTimeout,
                'body' => $message->body,
            ]
        );

        $headers = array_merge(
            [
                // hardcoded for BC, not provided by symfony/http-client, nor needed
                'version' => '1.1',
                'status' => $response->getStatusCode(),
            ],
            $response->getHeaders()
        );

        return new Message(
            $headers,
            $response->getContent(),
        );
    }
}
