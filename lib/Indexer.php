<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Search\Common\Indexer as SearchIndexer;
use EzSystems\EzPlatformSolrSearchEngine\Handler as SearchHandler;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;
use RuntimeException;

class Indexer extends SearchIndexer
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Handler
     */
    protected $searchHandler;

    /**
     * Create search engine index.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int $iterationCount
     * @param bool $commit
     */
    public function createSearchIndex(OutputInterface $output, $iterationCount, $commit)
    {
        $output->writeln('Creating Solr Search Engine Index...');

        if (!$this->searchHandler instanceof SearchHandler) {
            throw new RuntimeException(sprintf('Expected to find an instance of %s, but found %s', SearchHandler::class, get_class($this->searchHandler)));
        }

        $stmt = $this->getContentDbFieldsStmt(['count(id)']);
        $totalCount = intval($stmt->fetchColumn());
        $stmt = $this->getContentDbFieldsStmt(['id', 'current_version']);

        $this->searchHandler->purgeIndex();

        $progress = new ProgressBar($output);
        $progress->start($totalCount);

        $i = 0;
        do {
            $contentObjects = [];
            for ($k = 0; $k <= $iterationCount; ++$k) {
                if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }
                try {
                    $contentObjects[] = $this->persistenceHandler->contentHandler()->load(
                        $row['id'],
                        $row['current_version']
                    );
                } catch (NotFoundException $e) {
                    $this->logWarning($progress, "Could not load current version of Content with id ${row['id']}, so skipped for indexing. Full exception: " . $e->getMessage());
                }
            }

            $documents = [];
            foreach ($contentObjects as $content) {
                try {
                    $documents[] = $this->searchHandler->generateDocument($content);
                } catch (NotFoundException $e) {
                    // Ignore content objects that have some sort of missing data on it
                    $this->logWarning($progress, 'Content with id ' . $content->versionInfo->id . ' has missing data, so skipped for indexing. Full exception: ' . $e->getMessage());
                }
            }
            if (!empty($documents)) {
                $this->searchHandler->bulkIndexDocuments($documents);
            }

            if ($commit) {
                $this->searchHandler->commit();
            }

            $progress->advance($k);
        } while (($i += $iterationCount) < $totalCount);
        $progress->finish();
        $output->writeln('');

        $output->writeln('Finished creating Solr Search Engine Index');
    }
}
