<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Slot;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;

/**
 * Base class for testing Slots.
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    private $contentHandlerMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    private $persistenceHandlerMock;

    /**
     * @var \eZ\Publish\SPI\Search\Handler
     */
    private $searchHandlerMock;

    /**
     * @return \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        return $this->getMock(Repository::class);
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPersistenceHandlerMock()
    {
        if ($this->persistenceHandlerMock === null) {
            $this->persistenceHandlerMock = $this->getMock(PersistenceHandler::class);
            $this->persistenceHandlerMock
                ->expects($this->any())
                ->method('contentHandler')
                ->willReturn($this->getContentHandlerMock())
            ;
        }

        return $this->persistenceHandlerMock;
    }

    /**
     * @return \eZ\Publish\SPI\Search\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSearchHandlerMock()
    {
        if ($this->searchHandlerMock === null) {
            $this->searchHandlerMock = $this->getMock(SearchHandler::class);
        }

        return $this->searchHandlerMock;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentHandlerMock()
    {
        if ($this->contentHandlerMock === null) {
            $this->contentHandlerMock = $this->getMock(ContentHandler::class);
        }

        return $this->contentHandlerMock;
    }
}
