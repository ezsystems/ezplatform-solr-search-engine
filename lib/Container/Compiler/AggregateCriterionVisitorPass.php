<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass will register Solr Storage criterion visitors.
 */
class AggregateCriterionVisitorPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition('ezpublish.search.solr.query.content.criterion_visitor.aggregate') &&
            !$container->hasDefinition('ezpublish.search.solr.query.location.criterion_visitor.aggregate')
        ) {
            return;
        }

        if ($container->hasDefinition('ezpublish.search.solr.query.content.criterion_visitor.aggregate')) {
            $aggregateContentCriterionVisitorDefinition = $container->getDefinition(
                'ezpublish.search.solr.query.content.criterion_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ezpublish.search.solr.query.content.criterion_visitor'
            );

            $this->addHandlers($aggregateContentCriterionVisitorDefinition, $visitors);
        }

        if ($container->hasDefinition('ezpublish.search.solr.query.location.criterion_visitor.aggregate')) {
            $aggregateLocationCriterionVisitorDefinition = $container->getDefinition(
                'ezpublish.search.solr.query.location.criterion_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ezpublish.search.solr.query.location.criterion_visitor'
            );

            $this->addHandlers($aggregateLocationCriterionVisitorDefinition, $visitors);
        }
    }

    protected function addHandlers(Definition $definition, $handlers)
    {
        foreach ($handlers as $id => $attributes) {
            $definition->addMethodCall('addVisitor', array(new Reference($id)));
        }
    }
}
