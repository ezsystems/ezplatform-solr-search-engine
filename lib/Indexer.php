<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Search\Common\IncrementalIndexer;
use EzSystems\EzPlatformSolrSearchEngine\Handler as SolrSearchHandler;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\FieldType\Exceptions\InvalidIndexDataException;
use Psr\Log\LoggerInterface;

class Indexer extends IncrementalIndexer
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Handler
     */
    protected $searchHandler;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        LoggerInterface $logger,
        PersistenceHandler $persistenceHandler,
        DatabaseHandler $databaseHandler,
        SolrSearchHandler $searchHandler
    ) {
        parent::__construct($logger, $persistenceHandler, $databaseHandler, $searchHandler);
        $this->logger = $logger;
    }

    public function getName()
    {
        return 'eZ Platform Solr Search Engine';
    }

    public function purge()
    {
        $this->searchHandler->purgeIndex();
    }

    public function updateSearchIndex(array $contentIds, $commit, $continueOnError = false)
    {
        $documents = [];
        $unindexableContentIds = [];
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
            } catch (InvalidIndexDataException $indexDataException) {
                $unindexableContentIds[] = $contentId;
                if (!$continueOnError) {
                    $this->logger->error($indexDataException->getMessage());
                    break;
                }
                $this->logger->warning($indexDataException->getMessage());
            }
        }

        if (!empty($documents)) {
            $this->searchHandler->bulkIndexDocuments($documents);
        }

        if ($commit) {
            $this->searchHandler->commit(true);
        }

        return $unindexableContentIds;
    }
}
