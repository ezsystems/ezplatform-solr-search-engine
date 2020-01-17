<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Visits the ContentTypeIdentifier criterion.
 */
class ContentTypeIdentifierIn extends CriterionVisitor
{
    /**
     * ContentType handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create from content type handler and field registry.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(Handler $contentTypeHandler, LoggerInterface $logger = null)
    {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\ContentTypeIdentifier
            && (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $invalidIdentifiers = [];
        $contentTypeHandler = $this->contentTypeHandler;

        $idQueries = array_map(
            static function ($id) {
                return 'content_type_id_id:"' . $id . '"';
            },
            array_filter(
                $criterion->value,
                function ($value) use ($contentTypeHandler, &$invalidIdentifiers) {
                    try {
                        return $contentTypeHandler->loadByIdentifier($value)->id;
                    } catch (NotFoundException $e) {
                        // Filter out non-existing content types, but track for code below
                        $invalidIdentifiers[] = $value;

                        return false;
                    }
                }
            )
        );

        if (count($invalidIdentifiers) > 0) {
            $this->logger->warning(
                sprintf(
                    'Invalid content type identifiers provided for ContentTypeIdentifier criterion: %s',
                    implode(', ', $invalidIdentifiers)
                )
            );
        }

        if (count($idQueries) === 0) {
            return '(NOT *:*)';
        }

        return '(' . implode(' OR ', $idQueries) . ')';
    }
}
