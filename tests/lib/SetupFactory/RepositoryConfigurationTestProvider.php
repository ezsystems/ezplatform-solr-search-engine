<?php

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Class RepositoryConfigurationTestProvider
 * extends RepositoryConfigurationProvider because argument 1 passed to EzSystems\EzPlatformSolrSearchEngineBundle\ApiLoader\SolrEngineFactory::__construct() must be an instance of eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider
 * implements ConfigResolverInterface because parent __contstruct() expects such object in parameter 1
 *
 * Only constructor and getRepositoryConfig() is ever called in this class
 *
 */
class RepositoryConfigurationTestProvider extends RepositoryConfigurationProvider implements ConfigResolverInterface
{
    private $searchEngine;

    function __construct($searchEngine)
    {
        parent::__construct($this, []);
        $this->searchEngine = $searchEngine;
    }

    public function getRepositoryConfig()
    {
        return ['search' => ['engine' => $this->searchEngine] ];
    }

    public function getParameter($paramName, $namespace = null, $scope = null)
    {
        return '';
    }

    public function hasParameter($paramName, $namespace = null, $scope = null)
    {
        return false;
    }

    public function setDefaultNamespace($defaultNamespace)
    {
    }

    public function getDefaultNamespace()
    {
        return '';
    }
}
