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
 * This compiler pass will register Solr Storage sort clause visitors.
 */
class AggregateSortClauseVisitorPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition('ezpublish.search.solr.query.content.sort_clause_visitor.aggregate') &&
            !$container->hasDefinition('ezpublish.search.solr.query.location.sort_clause_visitor.aggregate')
        ) {
            return;
        }

        if ($container->hasDefinition('ezpublish.search.solr.query.content.sort_clause_visitor.aggregate')) {
            $aggregateContentSortClauseVisitorDefinition = $container->getDefinition(
                'ezpublish.search.solr.query.content.sort_clause_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ezpublish.search.solr.query.content.sort_clause_visitor'
            );

            $this->addHandlers($aggregateContentSortClauseVisitorDefinition, $visitors);
        }

        if ($container->hasDefinition('ezpublish.search.solr.query.location.sort_clause_visitor.aggregate')) {
            $aggregateLocationSortClauseVisitorDefinition = $container->getDefinition(
                'ezpublish.search.solr.query.location.sort_clause_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ezpublish.search.solr.query.location.sort_clause_visitor'
            );

            $this->addHandlers($aggregateLocationSortClauseVisitorDefinition, $visitors);
        }
    }

    protected function addHandlers(Definition $definition, $handlers)
    {
        foreach ($handlers as $id => $attributes) {
            $definition->addMethodCall('addVisitor', array(new Reference($id)));
        }
    }
}
