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

/**
 * Base compiler pass for aggregate document field mappers.
 */
abstract class BaseFieldMapperPass implements CompilerPassInterface
{
    /**
     * Service ID of the aggregate plugin.
     */
    const AGGREGATE_MAPPER_SERVICE_ID = null;

    /**
     * Service tag of plugins registering to the aggregate one.
     */
    const AGGREGATE_MAPPER_SERVICE_TAG = null;

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::AGGREGATE_MAPPER_SERVICE_ID)) {
            return;
        }

        $aggregateMapperDefinition = $container->getDefinition(static::AGGREGATE_MAPPER_SERVICE_ID);
        $taggedMapperServiceIds = $container->findTaggedServiceIds(static::AGGREGATE_MAPPER_SERVICE_TAG);

        foreach ($taggedMapperServiceIds as $id => $attributes) {
            $aggregateMapperDefinition->addMethodCall(
                'addMapper',
                [
                    new Reference($id),
                ]
            );
        }
    }
}
