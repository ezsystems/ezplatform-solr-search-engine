<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway\DistributionStrategy\DistributionStrategy;

use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\DocumentRouter\ImplicitDocumentRouter;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;
use PHPUnit\Framework\TestCase;

class ImplicitDocumentRouterTest extends TestCase
{
    private const ROUTER_FIELD_NAME = 'target_shard';

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\DocumentRouter\ImplicitDocumentRouter */
    private $router;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointResolver;

    protected function setUp()
    {
        $this->endpointResolver = $this->createMock(EndpointResolver::class);
        $this->router = new ImplicitDocumentRouter($this->endpointResolver, self::ROUTER_FIELD_NAME);
    }

    public function testProcessDocument()
    {
        $input = new Document();
        $input->languageCode = 'eng-GB';

        $this->endpointResolver
            ->method('getIndexingTarget')
            ->with($input->languageCode)
            ->willReturn('default@shard-en');

        $output = $this->router->processDocument($input);

        $this->assertCount(count($input->fields) + 1, $output->fields);
        $this->assertEquals(new Field(
            self::ROUTER_FIELD_NAME,
            'shard-en',
            new FieldType\IdentifierField()
        ), $output->fields[count($output->fields) - 1]);
    }

    public function testProcessMainTranslationDocument()
    {
        $this->endpointResolver
            ->method('getMainLanguagesEndpoint')
            ->willReturn('default@shard-main');

        $input = new Document();
        $output = $this->router->processMainTranslationDocument($input);

        $this->assertCount(count($input->fields) + 1, $output->fields);
        $this->assertEquals(new Field(
            self::ROUTER_FIELD_NAME,
            'shard-main',
            new FieldType\IdentifierField()
        ), $output->fields[count($output->fields) - 1]);
    }
}
