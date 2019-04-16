<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway\DistributionStrategy\DistributionStrategy;

use eZ\Publish\SPI\Search\Document;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\DocumentRouter\NullDocumentRouter;
use PHPUnit\Framework\TestCase;

class NullDocumentRouterTest extends TestCase
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\DocumentRouter\NullDocumentRouter */
    private $router;

    /** @var \eZ\Publish\SPI\Search\Document */
    private $document;

    protected function setUp()
    {
        $this->router = new NullDocumentRouter();
        $this->document = new Document();
    }

    public function testProcessDocument()
    {
        $this->assertEquals($this->document, $this->router->processDocument($this->document));
    }

    public function testProcessMainTranslationDocument()
    {
        $this->assertEquals($this->document, $this->router->processMainTranslationDocument($this->document));
    }
}
