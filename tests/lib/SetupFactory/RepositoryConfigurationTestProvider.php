<?php

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory;


class RepositoryConfigurationTestProvider
{
    private $searchEngine;

    function __construct($searchEngine)
    {
        $this->searchEngine = $searchEngine;
    }

    public function getRepositoryConfig()
    {
        return ['search' => ['engine' => $this->searchEngine] ];
    }
}
