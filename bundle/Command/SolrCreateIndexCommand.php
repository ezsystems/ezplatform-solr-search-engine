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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use EzSystems\EzPlatformSolrSearchEngine\Handler as SolrSearchEngineHandler;
use RuntimeException;
use PDO;

class SolrCreateIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezplatform:solr_create_index')
            ->setDescription('Indexes the configured database in configured Solr index')
            ->addArgument('bulk_count', InputArgument::OPTIONAL, 'Number of Content objects indexed at once', 5)
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> indexes current configured database in configured Solr storage.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bulkCount = $input->getArgument('bulk_count');

        /** @var \eZ\Publish\SPI\Search\Handler $searchHandler */
        $searchHandler = $this->getContainer()->get('ezpublish.spi.search');
        if (!$searchHandler instanceof SolrSearchEngineHandler) {
            throw new RuntimeException(
                'Expected to find Solr Search Engine but found something else. ' .
                "Did you forget to configure the repository with 'solr' search engine?"
            );
        }

        /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandler */
        $persistenceHandler = $this->getContainer()->get('ezpublish.api.persistence_handler');
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get('ezpublish.connection');

        // Indexing Content
        $query = $databaseHandler->createSelectQuery();
        $query->select('count(id)')
            ->from('ezcontentobject')
            ->where($query->expr->eq('status', ContentInfo::STATUS_PUBLISHED));
        $stmt = $query->prepare();
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        $query = $databaseHandler->createSelectQuery();
        $query->select('id', 'current_version')
            ->from('ezcontentobject')
            ->where($query->expr->eq('status', ContentInfo::STATUS_PUBLISHED));

        $stmt = $query->prepare();
        $stmt->execute();

        /** @var \EzSystems\EzPlatformSolrSearchEngine\Handler $searchHandler */
        $searchHandler->purgeIndex();

        $output->writeln('Indexing Content...');

        /** @var \Symfony\Component\Console\Helper\ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $totalCount);
        $i = 0;
        do {
            $contentObjects = array();

            for ($k = 0; $k <= $bulkCount; ++$k) {
                if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }

                $contentObjects[] = $persistenceHandler->contentHandler()->load(
                    $row['id'],
                    $row['current_version']
                );
            }

            if (!empty($contentObjects)) {
                $searchHandler->bulkIndexContent($contentObjects);
            }

            $progress->advance($k);
        } while (($i += $bulkCount) < $totalCount);

        // Make changes available for search
        $searchHandler->commit();

        $progress->finish();
    }
}
