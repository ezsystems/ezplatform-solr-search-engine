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
 * This compiler pass will register all tagged plugins for the native document mapper.
 */
class DocumentMapperPluginPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.search.solr.document_mapper.native')) {
            return;
        }

        $fieldRegistryDefinition = $container->getDefinition(
            'ezpublish.search.solr.document_mapper.native'
        );

        $plugins = $container->findTaggedServiceIds('ezpublish.search.solr.document_mapper_plugin');

        foreach ($plugins as $id => $attributes) {
            $fieldRegistryDefinition->addMethodCall(
                'addPlugin',
                array(
                    new Reference($id),
                )
            );
        }
    }
}
