<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Gateway;

use EzSystems\EzPlatformSolrSearchEngine\Gateway;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class GatewayRegistryTest extends TestCase
{
    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry::addGateway
     */
    public function testAddGateway(): void
    {
        $registry = new GatewayRegistry();
        $registry->addGateway('connection1', $this->getGatewayMock());

        $this->assertCount(1, $registry->getGateways());
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry::getGateway
     */
    public function testGetGateway(): void
    {
        $registry = new GatewayRegistry();
        $registry->addGateway('connection1', $this->getGatewayMock());

        $this->assertInstanceOf(Gateway::class, $registry->getGateway('connection1'));
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry::getGateway
     */
    public function testGetGatewayForMissingConnection(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $registry = new GatewayRegistry();
        $registry->getGateway('connection1');
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry::hasGateway
     */
    public function testHasGateway(): void
    {
        $registry = new GatewayRegistry();
        $registry->addGateway('connection1', $this->getGatewayMock());

        $this->assertTrue($registry->hasGateway('connection1'));
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry::setGateways
     */
    public function testSetGateways(): void
    {
        $gateways = ['connection1' => $this->getGatewayMock()];

        $registry = new GatewayRegistry();
        $registry->setGateways($gateways);

        $this->assertEquals($gateways, $registry->getGateways());
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry::getGateways
     */
    public function testGetGateways(): void
    {
        $registry = new GatewayRegistry(['connection1' => $this->getGatewayMock()]);

        $this->assertCount(1, $registry->getGateways());
    }

    /**
     * @return \EzSystems\EzPlatformSolrSearchEngine\Gateway|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getGatewayMock(): Gateway
    {
        return $this->createMock(Gateway::class);
    }
}
