<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway\DistributionStrategy;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\DocumentRouter\NullDocumentRouter;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\StandaloneDistributionStrategy;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StandaloneDistributionStrategyTest extends TestCase
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\StandaloneDistributionStrategy */
    private $distributionStrategy;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointResolver;

    protected function setUp()
    {
        $this->endpointResolver = $this->createEndpointRegistry();
        $this->distributionStrategy = new StandaloneDistributionStrategy($this->endpointResolver);
    }

    public function testGetSearchTargets()
    {
        $this->assertEquals([
            '127.0.0.65:8983/solr/collection1',
            '127.0.0.66:8983/solr/collection1',
            '127.0.0.67:8983/solr/collection1',
        ], $this->distributionStrategy->getSearchTargets(['A', 'B', 'C']));
    }

    public function testGetDocumentRouter()
    {
        $this->assertInstanceOf(NullDocumentRouter::class, $this->distributionStrategy->getDocumentRouter());
    }

    private function createEndpointRegistry(): MockObject
    {
        $endpointRegistry = $this->createMock(EndpointRegistry::class);
        $endpointRegistry
            ->method('getEndpoint')
            ->willReturnCallback(function ($name) {
                $endpoint = $this->createMock(Endpoint::class);
                $endpoint->method('getIdentifier')->willReturn('127.0.0.' . ord($name) . ':8983/solr/collection1');

                return $endpoint;
            });

        return $endpointRegistry;
    }
}
