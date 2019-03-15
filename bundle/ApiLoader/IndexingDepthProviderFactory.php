<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngineBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class IndexingDepthProviderFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider
     */
    private $repositoryConfigurationProvider;

    /**
     * @var string
     */
    private $defaultConnection;

    /**
     * @var string
     */
    private $indexingDepthProviderClass;

    /**
     * @param \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider $repositoryConfigurationProvider
     * @param string $defaultConnection
     * @param string $indexingDepthProviderClass
     */
    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        string $defaultConnection,
        string $indexingDepthProviderClass
    ) {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->indexingDepthProviderClass = $indexingDepthProviderClass;
    }

    public function buildService()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $this->defaultConnection;
        if (isset($repositoryConfig['search']['connection'])) {
            $connection = $repositoryConfig['search']['connection'];
        }

        return new $this->indexingDepthProviderClass(
            $this->container->getParameter(
                "ez_search_engine_solr.connection.{$connection}.indexing_depth.map"
            ),
            $this->container->getParameter(
                "ez_search_engine_solr.connection.{$connection}.indexing_depth.default"
            )
        );
    }
}
