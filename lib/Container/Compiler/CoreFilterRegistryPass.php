<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Container\Compiler;

use EzSystems\EzPlatformSolrSearchEngine\CoreFilter\CoreFilterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

final class CoreFilterRegistryPass implements CompilerPassInterface
{
    public const CORE_FILTER_SERVICE_TAG = 'ezpublish.search.solr.core_filter';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(CoreFilterRegistry::class)) {
            return;
        }

        $coreFilterRegistryDefinition = $container->getDefinition(CoreFilterRegistry::class);

        $coreFilters = $container->findTaggedServiceIds(self::CORE_FILTER_SERVICE_TAG);

        foreach ($coreFilters as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['connection'])) {
                    throw new LogicException(
                        "'ezpublish.search.solr.core_filter' service tag needs a 'connection' attribute " .
                        'to identify the Gateway.'
                    );
                }

                $coreFilterRegistryDefinition->addMethodCall(
                    'addCoreFilter',
                    [
                        $attribute['connection'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
