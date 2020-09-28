<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngineBundle;

use eZ\Publish\Core\Base\Container\Compiler\Search\AggregateFieldValueMapperPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\SearchEngineSignalSlotPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateCriterionVisitorPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateFacetBuilderVisitorPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateSortClauseVisitorPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\EndpointRegistryPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\FieldMapperPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzSystemsEzPlatformSolrSearchEngineBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FieldMapperPass\BlockFieldMapperPass());
        $container->addCompilerPass(new FieldMapperPass\BlockTranslationFieldMapperPass());
        $container->addCompilerPass(new FieldMapperPass\ContentFieldMapperPass());
        $container->addCompilerPass(new FieldMapperPass\ContentTranslationFieldMapperPass());
        $container->addCompilerPass(new FieldMapperPass\LocationFieldMapperPass());
        $container->addCompilerPass(new AggregateCriterionVisitorPass());
        $container->addCompilerPass(new AggregateFacetBuilderVisitorPass());
        $container->addCompilerPass(new AggregateSortClauseVisitorPass());
        $container->addCompilerPass(new EndpointRegistryPass());

        $container->addCompilerPass(new AggregateFieldValueMapperPass());
        $container->addCompilerPass(new FieldRegistryPass());
        $container->addCompilerPass(new SearchEngineSignalSlotPass('solr'));
    }

    public function getContainerExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = new DependencyInjection\EzSystemsEzPlatformSolrSearchEngineExtension();
        }

        return $this->extension;
    }
}
