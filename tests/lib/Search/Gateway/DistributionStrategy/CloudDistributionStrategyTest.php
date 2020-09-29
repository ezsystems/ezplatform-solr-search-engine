<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway\DistributionStrategy;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\CloudDistributionStrategy;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;
use PHPUnit\Framework\TestCase;

class CloudDistributionStrategyTest extends TestCase
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\CloudDistributionStrategy */
    private $distributionStrategy;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointResolver;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointRegistry;

    protected function setUp()
    {
        $this->endpointResolver = $this->createMock(EndpointResolver::class);

        $this->endpointRegistry = $this->createMock(EndpointRegistry::class);
        $this->endpointRegistry
            ->method('getEndpoint')
            ->willReturnCallback(function ($name) {
                return new Endpoint([
                    'core' => 'collection_' . $name,
                ]);
            });

        $this->distributionStrategy = new CloudDistributionStrategy(
            $this->endpointRegistry,
            $this->endpointResolver
        );
    }

    public function testGetSearchParameters()
    {
        $this->endpointResolver
            ->expects($this->once())
            ->method('getEndpoints')
            ->willReturn(['en', 'de', 'fr', 'pl']);

        $parameters = [
            'wt' => 'json',
            'indent' => true,
        ];

        $this->assertEquals([
            'wt' => 'json',
            'indent' => true,
            'collection' => 'collection_en,collection_de,collection_fr,collection_pl',
        ], $this->distributionStrategy->getSearchParameters($parameters));
    }

    public function testGetSearchParametersWithLanguageSettings()
    {
        $languagesSettings = [
            'languages' => ['eng-GB', 'pol-PL'],
        ];

        $this->endpointResolver
            ->expects($this->once())
            ->method('getSearchTargets')
            ->with($languagesSettings)
            ->willReturn(['en', 'pl']);

        $parameters = [
            'wt' => 'json',
            'indent' => true,
        ];

        $this->assertEquals([
            'wt' => 'json',
            'indent' => true,
            'collection' => 'collection_en,collection_pl',
        ], $this->distributionStrategy->getSearchParameters($parameters, $languagesSettings));
    }
}
