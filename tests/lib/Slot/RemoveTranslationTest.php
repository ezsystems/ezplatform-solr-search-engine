<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Slot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\RemoveTranslationSignal;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use EzSystems\EzPlatformSolrSearchEngine\Slot\RemoveTranslation;

/**
 * RemoveTranslation Slot Test.
 */
class RemoveTranslationTest extends TestCase
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Slot\RemoveTranslation
     */
    private $slot;

    /**
     * Check if required signal exists due to BC.
     */
    public static function setUpBeforeClass(): void
    {
        if (!class_exists(RemoveTranslationSignal::class)) {
            self::markTestSkipped('RemoveTranslationSignal does not exist');
        }
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->slot = new RemoveTranslation(
            $this->getRepositoryMock(),
            $this->getPersistenceHandlerMock(),
            $this->getSearchHandlerMock()
        );
    }

    /**
     * Test receiving RemoveTranslationSignal.
     *
     * @covers \EzSystems\EzPlatformSolrSearchEngine\Slot\RemoveTranslation::receive
     */
    public function testReceive()
    {
        $contentHandlerMock = $this->getContentHandlerMock();

        $contentHandlerMock
            ->expects($this->once())
            ->method('loadContentInfo')
            ->willReturn(
                new ContentInfo(
                    [
                        'id' => 2,
                        'currentVersionNo' => 1,
                        'isPublished' => true,
                    ]
                )
            )
        ;

        $content = new SPIContent();

        $contentHandlerMock
            ->expects($this->once())
            ->method('load')
            ->with(2, 1)
            ->willReturn($content)
        ;

        $this
            ->getSearchHandlerMock()
            ->expects($this->once())
            ->method('indexContent')
            ->with($content)
        ;

        $this->slot->receive(
            new RemoveTranslationSignal(['contentId' => 2, 'languageCode' => 'eng-US'])
        );
    }
}
