<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngineBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Publish\SPI\Persistence\Content\Handler;
use EzSystems\EzPlatformSolrSearchEngine\CoreFilter\CoreFilterRegistry;
use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor;

class SolrEngineFactory
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /** @var string */
    private $defaultConnection;

    /** @var string */
    private $searchEngineClass;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry */
    private $gatewayRegistry;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\CoreFilter\CoreFilterRegistry */
    private $coreFilterRegistry;

    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $contentHandler;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper */
    private $documentMapper;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor */
    private $contentResultExtractor;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor */
    private $locationResultExtractor;

    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        $defaultConnection,
        $searchEngineClass,
        GatewayRegistry $gatewayRegistry,
        CoreFilterRegistry $coreFilterRegistry,
        Handler $contentHandler,
        DocumentMapper $documentMapper,
        ResultExtractor $contentResultExtractor,
        ResultExtractor $locationResultExtractor
    ) {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->searchEngineClass = $searchEngineClass;
        $this->gatewayRegistry = $gatewayRegistry;
        $this->coreFilterRegistry = $coreFilterRegistry;
        $this->contentHandler = $contentHandler;
        $this->documentMapper = $documentMapper;
        $this->contentResultExtractor = $contentResultExtractor;
        $this->locationResultExtractor = $locationResultExtractor;
    }

    public function buildEngine()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $repositoryConfig['search']['connection'] ?? $this->defaultConnection;

        $gateway = $this->gatewayRegistry->getGateway($connection);
        $coreFilter = $this->coreFilterRegistry->getCoreFilter($connection);

        return new $this->searchEngineClass(
            $gateway,
            $this->contentHandler,
            $this->documentMapper,
            $this->contentResultExtractor,
            $this->locationResultExtractor,
            $coreFilter
        );
    }
}
