<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

class GatewayRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.search.solr.gateway.registry')) {
            return;
        }

        $gatewayRegistryDefinition = $container->getDefinition('ezpublish.search.solr.gateway.registry');

        $gateways = $container->findTaggedServiceIds('ezpublish.search.solr.gateway');

        foreach ($gateways as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['connection'])) {
                    throw new LogicException(
                        "'ezpublish.search.solr.gateway' service tag needs an 'connection' attribute " .
                        'to identify the Gateway. None given.'
                    );
                }

                $gatewayRegistryDefinition->addMethodCall(
                    'addGateway',
                    [
                        $attribute['connection'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
