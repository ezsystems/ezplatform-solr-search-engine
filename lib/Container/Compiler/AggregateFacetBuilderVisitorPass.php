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

/**
 * This compiler pass will register Solr Storage facet builder visitors.
 */
class AggregateFacetBuilderVisitorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->processVisitors($container, 'content');
        $this->processVisitors($container, 'location');
    }

    private function processVisitors(ContainerBuilder $container, $name = 'content')
    {
        if (!$container->hasDefinition("ezpublish.search.solr.query.${name}.facet_builder_visitor.aggregate")) {
            return;
        }

        $aggregateFacetBuilderVisitorDefinition = $container->getDefinition(
            "ezpublish.search.solr.query.${name}.facet_builder_visitor.aggregate"
        );

        foreach ($container->findTaggedServiceIds("ezpublish.search.solr.query.${name}.facet_builder_visitor") as $id => $attributes) {
            $aggregateFacetBuilderVisitorDefinition->addMethodCall(
                'addVisitor',
                [
                    new Reference($id),
                ]
            );
        }
    }
}
