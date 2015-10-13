<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\SolrSearchEngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Publish\Core\Search\Solr\Container\Compiler\AggregateCriterionVisitorPass;
use eZ\Publish\Core\Search\Solr\Container\Compiler\AggregateFacetBuilderVisitorPass;
use eZ\Publish\Core\Search\Solr\Container\Compiler\AggregateFieldValueMapperPass;
use eZ\Publish\Core\Search\Solr\Container\Compiler\AggregateSortClauseVisitorPass;
use eZ\Publish\Core\Search\Solr\Container\Compiler\EndpointRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\SignalSlotPass;

class EzSystemsSolrSearchEngineBundle extends Bundle
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
            $this->extension = new DependencyInjection\EzSystemsSolrSearchEngineExtension();
        }

        return $this->extension;
    }
}
