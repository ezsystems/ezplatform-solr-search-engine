<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Container\Compiler;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

final class GatewayRegistryPass implements CompilerPassInterface
{
    public const SOLR_GATEWAY_SERVICE_TAG = 'ezpublish.search.solr.gateway';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(GatewayRegistry::class)) {
            return;
        }

        $gatewayRegistryDefinition = $container->getDefinition(GatewayRegistry::class);

        $gateways = $container->findTaggedServiceIds(self::SOLR_GATEWAY_SERVICE_TAG);

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
