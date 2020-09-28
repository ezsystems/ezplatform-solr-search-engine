<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Container\Compiler;

use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateFacetBuilderVisitorPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AggregateFacetBuilderVisitorPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition(
            'ezpublish.search.solr.query.content.facet_builder_visitor.aggregate',
            new Definition()
        );
        $this->setDefinition(
            'ezpublish.search.solr.query.location.facet_builder_visitor.aggregate',
            new Definition()
        );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AggregateFacetBuilderVisitorPass());
    }

    public function testAddVisitor()
    {
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.search.solr.query.content.facet_builder_visitor');
        $this->setDefinition($serviceId, $def);

        $serviceId2 = 'service_id2';
        $def = new Definition();
        $def->addTag('ezpublish.search.solr.query.location.facet_builder_visitor');
        $this->setDefinition($serviceId2, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.search.solr.query.content.facet_builder_visitor.aggregate',
            'addVisitor',
            [new Reference($serviceId)]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.search.solr.query.location.facet_builder_visitor.aggregate',
            'addVisitor',
            [new Reference($serviceId2)]
        );
    }
}
