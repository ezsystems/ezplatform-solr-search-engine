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
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
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

    /** @var int */
    private $connectionRetry;

    /** @var int */
    private $retryWaitMs;

    /** @var \Symfony\Contracts\HttpClient\HttpClientInterface */
    private $client;

    /**
     * Stream constructor.
     *
     * @param int $timeout Timeout for connection in seconds.
     * @param int $retry Number of times to re-try connection.
     * @param int $retryWaitMs Time in milliseconds.
     */
    public function __construct(
        HttpClientInterface $client,
        int $timeout = 10,
        int $retry = 5,
        int $retryWaitMs = 100
    ) {
        $this->client = $client;
        $this->connectionTimeout = $timeout;
        $this->connectionRetry = $retry;
        $this->retryWaitMs = $retryWaitMs;
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
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     */
    public function request($method, Endpoint $endpoint, $path, Message $message = null): Message
    {
        $message = $message ?? new Message();

        // We'll try to reach backend several times before throwing exception.
        $i = 0;
        do {
            ++$i;
            try {
                if (
                    $responseMessage = $this->getResponseMessage(
                        $method,
                        $endpoint,
                        $path,
                        $message
                    )
                ) {
                    return $responseMessage;
                }
            } catch (TransportExceptionInterface $e) {
                $this->logger->warning(
                    sprintf(
                        'Connection attempt #%d to %s failed, retrying after %d ms',
                        $i,
                        $endpoint->getURL(),
                        $this->retryWaitMs
                    )
                );
            }
            usleep($this->retryWaitMs * 1000);
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
