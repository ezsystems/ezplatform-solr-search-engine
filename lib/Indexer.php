<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Search\Common\IncrementalIndexer;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use EzSystems\EzPlatformSolrSearchEngine\Handler as SolrSearchHandler;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use Psr\Log\LoggerInterface;
use Exception;

class Indexer extends IncrementalIndexer
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Handler
     */
    protected $searchHandler;

    public function __construct(
        LoggerInterface $logger,
        PersistenceHandler $persistenceHandler,
        Connection $connection,
        SolrSearchHandler $searchHandler
    ) {
        parent::__construct($logger, $persistenceHandler, $connection, $searchHandler);
    }

    public function getName()
    {
        return 'eZ Platform Solr Search Engine';
    }

    public function purge()
    {
        $this->searchHandler->purgeIndex();
    }

    public function updateSearchIndex(array $contentIds, $commit)
    {
        $documents = [];
        $contentHandler = $this->persistenceHandler->contentHandler();
        foreach ($contentIds as $contentId) {
            try {
                $info = $contentHandler->loadContentInfo($contentId);
                if ($info->isPublished) {
                    $content = $contentHandler->load($contentId, $info->currentVersionNo);
                    $documents[] = $this->searchHandler->generateDocument($content);
                } else {
                    $this->searchHandler->deleteContent($contentId);
                }
            } catch (NotFoundException $e) {
                $this->searchHandler->deleteContent($contentId);
            } catch (Exception $e) {
                $context = [
                    'contentId' => $contentId,
                    'error' => $e->getMessage(),
                ];
                $this->logger->error('Unable to index the content', $context);
            }
        }

        if (!empty($documents)) {
            $this->searchHandler->bulkIndexDocuments($documents);
        }

        if ($commit) {
            $this->searchHandler->commit(true);
        }
    }
}
