<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\FieldAggregationInterface;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;
use RuntimeException;

final class ContentFieldAggregationFieldResolver implements AggregationFieldResolver
{
    /** @var \eZ\Publish\Core\Search\Common\FieldNameResolver */
    private $fieldNameResolver;

    /** @var string */
    private $searchFieldName;

    public function __construct(FieldNameResolver $fieldNameResolver, string $searchFieldName)
    {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->searchFieldName = $searchFieldName;
    }

    public function resolveTargetField(AggregationInterface $aggregation): string
    {
        if (!($aggregation instanceof FieldAggregationInterface)) {
            throw new RuntimeException('Expected instance of ' . FieldAggregationInterface::class . ' , got ' . get_class($aggregation));
        }

        $searchFieldName = $this->fieldNameResolver->getAggregationFieldName(
            $aggregation->getContentTypeIdentifier(),
            $aggregation->getFieldDefinitionIdentifier(),
            $this->searchFieldName
        );

        if ($searchFieldName === null) {
            throw new RuntimeException('No searchable fields found for the provided aggregation target');
        }

        return $searchFieldName;
    }
}