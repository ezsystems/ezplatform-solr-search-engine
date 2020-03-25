<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use eZ\Publish\API\Repository\Tests\SearchServiceTranslationLanguageFallbackTest;
use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy as CoreLegacySetupFactory;
use eZ\Publish\Core\Base\Container\Compiler as BaseCompiler;
use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\SPI\Persistence;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler;
use EzSystems\EzPlatformSolrSearchEngine\Handler as SolrSearchHandler;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Used to setup the infrastructure for Repository Public API integration tests,
 * based on Repository with Legacy Storage Engine implementation.
 */
class LegacySetupFactory extends CoreLegacySetupFactory
{
    /**
     * Returns a configured repository for testing.
     *
     * @param bool $initializeFromScratch
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository($initializeFromScratch = true)
    {
        // Load repository first so all initialization steps are done
        $repository = parent::getRepository($initializeFromScratch);

        if ($initializeFromScratch) {
            $this->indexAll();
        }

        return $repository;
    }

    protected function externalBuildContainer(ContainerBuilder $containerBuilder)
    {
        $settingsPath = __DIR__ . '/../../../lib/Resources/config/container/';
        $testSettingsPath = __DIR__ . '/../Resources/config/';

        $solrLoader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));
        $solrLoader->load('solr.yml');

        $solrTestLoader = new YamlFileLoader($containerBuilder, new FileLocator($testSettingsPath));
        $solrTestLoader->load($this->getTestConfigurationFile());

        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\BlockFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\BlockTranslationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\ContentFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\ContentTranslationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\LocationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateCriterionVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateFacetBuilderVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateSortClauseVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\EndpointRegistryPass());
        $containerBuilder->addCompilerPass(new BaseCompiler\Search\AggregateFieldValueMapperPass());
        $containerBuilder->addCompilerPass(new BaseCompiler\Search\FieldRegistryPass());
    }

    private function getPersistenceContentHandler(
        ServiceContainer $serviceContainer
    ): Persistence\Content\Handler {
        /** @var \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler */
        $contentHandler = $serviceContainer->get('ezpublish.spi.persistence.content_handler');

        return $contentHandler;
    }

    private function getSearchHandler(ServiceContainer $serviceContainer): SolrSearchHandler
    {
        /** @var \EzSystems\EzPlatformSolrSearchEngine\Handler $searchHandler */
        $searchHandler = $serviceContainer->get('ezpublish.spi.search.solr');

        return $searchHandler;
    }

    private function getDatabaseConnection(ServiceContainer $serviceContainer): Connection
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $serviceContainer->get('ezpublish.persistence.connection');

        return $connection;
    }

    /**
     * Indexes all Content objects.
     */
    protected function indexAll(): void
    {
        $serviceContainer = $this->getServiceContainer();
        $contentHandler = $this->getPersistenceContentHandler($serviceContainer);
        $searchHandler = $this->getSearchHandler($serviceContainer);
        $connection = $this->getDatabaseConnection($serviceContainer);

        $query = $connection->createQueryBuilder();
        $query
            ->select('id')
            ->from(ContentGateway::CONTENT_ITEM_TABLE);

        $contentIds = array_map('intval', $query->execute()->fetchAll(FetchMode::COLUMN));

        $contentItems = $contentHandler->loadContentList($contentIds);

        $searchHandler->purgeIndex();
        $searchHandler->bulkIndexContent($contentItems);
        $searchHandler->commit();
    }

    protected function getTestConfigurationFile()
    {
        $isSolrCloud = getenv('SOLR_CLOUD');
        if ($isSolrCloud) {
            return 'cloud.yml';
        }

        $coresSetup = getenv('CORES_SETUP');
        switch ($coresSetup) {
            case SearchServiceTranslationLanguageFallbackTest::SETUP_DEDICATED:
                return 'multicore_dedicated.yml';
            case SearchServiceTranslationLanguageFallbackTest::SETUP_SHARED:
                return 'multicore_shared.yml';
            case SearchServiceTranslationLanguageFallbackTest::SETUP_SINGLE:
                return 'single_core.yml';
        }

        throw new RuntimeException("Backend cores setup '{$coresSetup}' is not handled");
    }
}
