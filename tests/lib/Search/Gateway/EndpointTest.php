<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway;

use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\Endpoint;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase;

/**
 * Test case for native endpoint resolver.
 */
class EndpointTest extends TestCase
{
    public function testEndpointDsnParsingWithAll()
    {
        $actual = new Endpoint(['dsn' => 'https://jura:pura@10.10.10.10:5434/jolr', 'core' => 'core0']);
        $expected = new Endpoint([
                'scheme' => 'https',
                'host' => '10.10.10.10',
                'port' => 5434,
                'user' => 'jura',
                'pass' => 'pura',
                'path' => '/jolr',
                'core' => 'core0',
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testEndpointDsnParsingWithoutUser()
    {
        $actual = new Endpoint(['dsn' => 'https://10.10.10.10:5434/jolr', 'core' => 'core0']);
        $expected = new Endpoint([
                'scheme' => 'https',
                'host' => '10.10.10.10',
                'port' => 5434,
                'user' => null,
                'pass' => null,
                'path' => '/jolr',
                'core' => 'core0',
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testEndpointDsnParsingWithFragment()
    {
        $actual = new Endpoint(['dsn' => 'https://10.10.10.10:5434/jolr#core1']);
        $expected = new Endpoint([
                'scheme' => 'https',
                'host' => '10.10.10.10',
                'port' => 5434,
                'user' => null,
                'pass' => null,
                'path' => '/jolr',
                'core' => 'core1',
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testEndpointDsnParsingOverridesAllIfSet()
    {
        $actual = new Endpoint([
            'dsn' => 'https://jura:pura@10.10.10.10:5434/jolr#core1',
            'scheme' => 'http',
            'host' => '127.1.1.1',
            'port' => 8983,
            'user' => 'ben',
            'pass' => 'pass',
            'path' => '/solr',
            'core' => 'core0',
        ]);
        $expected = new Endpoint([
                'scheme' => 'https',
                'host' => '10.10.10.10',
                'port' => 5434,
                'user' => 'jura',
                'pass' => 'pura',
                'path' => '/jolr',
                'core' => 'core1',
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testEndpointDsnParsingWithQuery()
    {
        $this->expectException(PropertyNotFoundException::class);

        $actual = new Endpoint(['dsn' => 'https://10.10.10.10:5434/jolr?query']);
    }
}
