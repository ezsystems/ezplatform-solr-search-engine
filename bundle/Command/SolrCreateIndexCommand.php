<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use EzSystems\EzPlatformSolrSearchEngine\Handler as SolrSearchEngineHandler;
use DateTime;
use RuntimeException;
use PDO;

/**
 * @deprecated since 1.2, use ezplatform:reindex command instead.
 */
class SolrCreateIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezplatform:solr_create_index')
            ->setDescription('Indexes the configured database in configured Solr index')
            ->addArgument('bulk_count', InputArgument::OPTIONAL, 'Number of Content objects indexed at once', 5)
            ->addOption('no-commit', null, InputOption::VALUE_NONE, 'Do not commit after each bulk iteration')
            ->addOption('no-purge', null, InputOption::VALUE_NONE, 'Do not purge before indexing, hence rather refresh index')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Index changes since a given time, any format understood by DateTime. Implies "no-purge".')
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> indexes current configured database in configured Solr storage.


DEPRECATED: Use ezplatform:reindex command instead, it gives many more options for indexing your content.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        @trigger_error(
            sprintf('%s is deprecated since 1.2. Use ezplatform:reindex command instead', $this->getName()),
            E_USER_DEPRECATED
        );

        $this->logger = $this->getContainer()->get('logger');

        $commit = !$input->getOption('no-commit');
        $purge = !$input->getOption('no-purge');
        $bulkCount = $input->getArgument('bulk_count');
        if (!is_numeric($bulkCount) || (int)$bulkCount < 1) {
            throw new RuntimeException("The 'bulk_count' argument should be > 0, you provided '{$bulkCount}'");
        }

        /** @var \eZ\Publish\SPI\Search\Handler $searchHandler */
        $searchHandler = $this->getContainer()->get('ezpublish.spi.search');
        if (!$searchHandler instanceof SolrSearchEngineHandler) {
            throw new RuntimeException(
                'Did not find Solr Search Engine. ' .
                "Did you configure the Repository with the 'solr' search engine?"
            );
        }

        /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandler */
        $persistenceHandler = $this->getContainer()->get('ezpublish.api.persistence_handler');
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get('ezpublish.connection');

        // Indexing Content
        $query = $databaseHandler->createSelectQuery();
        $where = $query->expr->eq('status', ContentInfo::STATUS_PUBLISHED);
        if ($since = $input->getOption('since')) {
            $date = new DateTime($since);
            $where = [
                $where,
                $query->expr->gte('modified', $date->getTimestamp()),
            ];
            $purge = false;
        }

        $query->select('count(id)')
            ->from('ezcontentobject')
            ->where($where);
        $stmt = $query->prepare();
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        $query = $databaseHandler->createSelectQuery();
        $query->select('id', 'current_version')
            ->from('ezcontentobject')
            ->where($where);
        $stmt = $query->prepare();
        $stmt->execute();

        if ($purge) {
            $output->writeln('Purging index before starting re-indexing (use no-purge to skip this)..');
            $searchHandler->purgeIndex();
        }

        $output->writeln('Indexing Content...');

        /** @var \Symfony\Component\Console\Helper\ProgressHelper $progress */
        $progress = new ProgressBar($output);
        $progress->start($totalCount);
        $i = 0;
        do {
            $contentObjects = array();

            for ($k = 0; $k < $bulkCount; ++$k) {
                if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }
                try {
                    $contentObjects[] = $persistenceHandler->contentHandler()->load(
                        $row['id'],
                        $row['current_version']
                    );
                } catch (NotFoundException $e) {
                    $this->logWarning($output, $progress, "Could not load current version of Content with id ${row['id']}, so skipped for indexing. Full exception: " . $e->getMessage());
                }
            }

            $documents = [];

            foreach ($contentObjects as $content) {
                try {
                    $documents[] = $searchHandler->generateDocument($content);
                } catch (NotFoundException $e) {
                    // Ignore content objects that have some sort of missing data on it
                    $this->logWarning($output, $progress, 'Content with id ' . $content->versionInfo->id . ' has missing data, so skipped for indexing. Full exception: ' . $e->getMessage());
                }
            }

            if (!empty($documents)) {
                $searchHandler->bulkIndexDocuments($documents);

                if ($commit) {
                    // Make the bulk changes available for search
                    $searchHandler->commit();
                }
            }

            $progress->advance($k);
        } while (($i += $bulkCount) < $totalCount);

        $progress->finish();
        $output->writeln('');
    }

    /**
     * Log warning while progress helper is running.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\ProgressBar $progress
     * @param $message
     */
    private function logWarning(OutputInterface $output, ProgressBar $progress, $message)
    {
        $progress->clear();
        $this->logger->warning($message);
        $progress->display();
    }

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
}
