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

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Solr Endpoints.
 */
class EndpointRegistryPass implements CompilerPassInterface
{
    /**
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition(
                'ezpublish.search.solr.gateway.endpoint_registry'
            )
        ) {
            return;
        }

        $fieldRegistryDefinition = $container->getDefinition(
            'ezpublish.search.solr.gateway.endpoint_registry'
        );

        $endpoints = $container->findTaggedServiceIds('ezpublish.search.solr.endpoint');

        foreach ($endpoints as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException("'ezpublish.search.solr.endpoint' service tag needs an 'alias' attribute " . 'to identify the Endpoint. None given.');
                }

                $fieldRegistryDefinition->addMethodCall(
                    'registerEndpoint',
                    [
                        $attribute['alias'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
