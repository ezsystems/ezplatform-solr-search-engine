<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngineBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * BoostFactorProvider service factory takes into account boost factor semantic configuration.
 */
class BoostFactorProviderFactory implements ContainerAwareInterface
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
    private $boostFactorProviderClass;

    /**
     * @param string $defaultConnection
     * @param string $boostFactorProviderClass
     */
    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        $defaultConnection,
        $boostFactorProviderClass
    ) {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->boostFactorProviderClass = $boostFactorProviderClass;
    }

    public function buildService()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $this->defaultConnection;
        if (isset($repositoryConfig['search']['connection'])) {
            $connection = $repositoryConfig['search']['connection'];
        }

        return new $this->boostFactorProviderClass(
            $this->container->getParameter(
                "ez_search_engine_solr.connection.{$connection}.boost_factor_map_id"
            )
        );
    }
}
