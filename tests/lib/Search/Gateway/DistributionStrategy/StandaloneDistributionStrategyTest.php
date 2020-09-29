<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway\DistributionStrategy;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\StandaloneDistributionStrategy;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StandaloneDistributionStrategyTest extends TestCase
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\StandaloneDistributionStrategy */
    private $distributionStrategy;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointRegistry;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointResolver;

    protected function setUp(): void
    {
        $this->endpointRegistry = $this->createEndpointRegistry();
        $this->endpointResolver = $this->createMock(EndpointResolver::class);

        $this->distributionStrategy = new StandaloneDistributionStrategy(
            $this->endpointRegistry,
            $this->endpointResolver
        );
    }

    public function testGetSearchTargets(): void
    {
        $this->endpointResolver
            ->expects($this->once())
            ->method('getEndpoints')
            ->willReturn(['A', 'B', 'C']);

        $actual = $this->distributionStrategy->getSearchParameters([
            'wt' => 'json',
            'indent' => true,
        ]);

        $this->assertEquals([
            'wt' => 'json',
            'indent' => true,
            'shards' => '127.0.0.65:8983/solr/collection1,127.0.0.66:8983/solr/collection1,127.0.0.67:8983/solr/collection1',
        ], $actual);
    }

    public function testGetSearchParametersWithLanguageSettings(): void
    {
        $languagesSettings = [
            'languages' => ['eng-GB', 'pol-PL'],
        ];

        $this->endpointResolver
            ->expects($this->once())
            ->method('getSearchTargets')
            ->with($languagesSettings)
            ->willReturn(['A', 'B']);

        $parameters = [
            'wt' => 'json',
            'indent' => true,
        ];

        $this->assertEquals([
            'wt' => 'json',
            'indent' => true,
            'shards' => '127.0.0.65:8983/solr/collection1,127.0.0.66:8983/solr/collection1',
        ], $this->distributionStrategy->getSearchParameters($parameters, $languagesSettings));
    }

    private function createEndpointRegistry(): MockObject
    {
        $endpointRegistry = $this->createMock(EndpointRegistry::class);
        $endpointRegistry
            ->method('getEndpoint')
            ->willReturnCallback(function ($name) {
                $endpoint = $this->createMock(Endpoint::class);
                $endpoint->method('getIdentifier')->willReturn('127.0.0.' . \ord($name) . ':8983/solr/collection1');

                return $endpoint;
            });

        return $endpointRegistry;
    }
}
