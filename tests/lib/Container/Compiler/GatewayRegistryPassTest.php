<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Container\Compiler;

use EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\GatewayRegistryPass;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class GatewayRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition(GatewayRegistry::class, new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new GatewayRegistryPass());
    }

    public function testAddGateway(): void
    {
        $definition = new Definition();
        $definition->addTag(GatewayRegistryPass::GATEWAY_SERVICE_TAG, ['connection' => 'connection1']);
        $this->setDefinition('service_1', $definition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            GatewayRegistry::class,
            'addGateway',
            ['connection1', new Reference('service_1')]
        );
    }
}
