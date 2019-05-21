<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway\DistributionStrategy;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\CloudDistributionStrategy;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;
use PHPUnit\Framework\TestCase;

class CloudDistributionStrategyTest extends TestCase
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\CloudDistributionStrategy */
    private $distributionStrategy;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointResolver;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter|\PHPUnit\Framework\MockObject\MockObject */
    private $documentRouter;

    protected function setUp()
    {
        $this->endpointResolver = $this->createMock(EndpointResolver::class);
        $this->endpointResolver
            ->method('getEntryEndpoint')
            ->willReturn('default');

        $this->documentRouter = $this->createMock(DocumentRouter::class);
        $this->distributionStrategy = new CloudDistributionStrategy(
            $this->endpointResolver,
            $this->documentRouter
        );
    }

    public function testGetSearchTargets()
    {
        $this->assertEquals([
            'shard-a',
            'shard-b',
            'shard-c',
        ], $this->distributionStrategy->getSearchTargets(['default@shard-a', 'default@shard-b', 'default@shard-c']));
    }

    public function testGetDocumentRouter()
    {
        $this->assertEquals($this->documentRouter, $this->distributionStrategy->getDocumentRouter());
    }
}
