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

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateCriterionVisitorPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateFacetBuilderVisitorPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateFieldValueMapperPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateSortClauseVisitorPass;
use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\EndpointRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\SignalSlotPass;

class EzSystemsEzPlatformSolrSearchEngineBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AggregateCriterionVisitorPass());
        $container->addCompilerPass(new AggregateFacetBuilderVisitorPass());
        $container->addCompilerPass(new AggregateFieldValueMapperPass());
        $container->addCompilerPass(new AggregateSortClauseVisitorPass());
        $container->addCompilerPass(new EndpointRegistryPass());

        $container->addCompilerPass(new FieldRegistryPass());
        $container->addCompilerPass(new SignalSlotPass());
    }

    public function getContainerExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = new DependencyInjection\EzSystemsEzPlatformSolrSearchEngineExtension();
        }

        return $this->extension;
    }
}
