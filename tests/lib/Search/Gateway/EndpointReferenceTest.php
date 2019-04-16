<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointReference;
use PHPUnit\Framework\TestCase;

class EndpointReferenceTest extends TestCase
{
    public function testConstruct()
    {
        $ref = new EndpointReference('127.0.0.1', 'shard');

        $this->assertEquals('127.0.0.1', $ref->endpoint);
        $this->assertEquals('shard', $ref->shard);
    }

    public function testFromString()
    {
        $refB = EndpointReference::fromString('127.0.0.1@shard');

        $this->assertEquals('127.0.0.1', $refB->endpoint);
        $this->assertEquals('shard', $refB->shard);
    }

    public function testFromStringIsCached()
    {
        $refA = EndpointReference::fromString('127.0.0.1@shard');
        $refB = EndpointReference::fromString('127.0.0.1@shard');

        $this->assertSame($refA, $refB);
    }

    public function testConversionToString()
    {
        $this->assertEquals('127.0.0.1', (string)new EndpointReference('127.0.0.1'));
        $this->assertEquals('127.0.0.1@shard', (string)new EndpointReference('127.0.0.1', 'shard'));
    }
}
