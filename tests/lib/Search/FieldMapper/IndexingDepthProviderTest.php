<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\FieldMapper;

use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\IndexingDepthProvider;
use PHPUnit\Framework\TestCase;

class IndexingDepthProviderTest extends TestCase
{
    public function testGetMaxDepthForContentType()
    {
        $indexingDepthProvider = $this->createIndexingDepthProvider();

        $this->assertEquals(2, $indexingDepthProvider->getMaxDepthForContent(
            $this->getContentTypeStub('article')
        ));

        $this->assertEquals(1, $indexingDepthProvider->getMaxDepthForContent(
            $this->getContentTypeStub('blog_post')
        ));
    }

    public function testGetMaxDepthForContentTypeReturnsDefaultValue()
    {
        $indexingDepthProvider = $this->createIndexingDepthProvider();

        $this->assertEquals(0, $indexingDepthProvider->getMaxDepthForContent(
            $this->getContentTypeStub('folder')
        ));
    }

    public function testGetMaxDepth()
    {
        $this->assertEquals(2, $this->createIndexingDepthProvider()->getMaxDepth());
    }

    private function createIndexingDepthProvider(): IndexingDepthProvider
    {
        return new IndexingDepthProvider([
            'article' => 2,
            'blog_post' => 1,
        ], 0);
    }

    private function getContentTypeStub($identifier): SPIContentType
    {
        return new SPIContentType([
            'identifier' => $identifier,
        ]);
    }
}
