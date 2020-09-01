<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver;

final class CountryFieldTermAggregationFieldResolver implements AggregationFieldResolver
{
    /** @var \eZ\Publish\Core\Search\Common\FieldNameResolver */
    private $fieldNameResolver;

    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation $aggregation
     */
    public function resolveTargetField(AggregationInterface $aggregation): string
    {
        return $this->fieldNameResolver->getAggregationFieldName(
            $aggregation->getContentTypeIdentifier(),
            $aggregation->getFieldDefinitionIdentifier(),
            $this->getSearchFieldName($aggregation)
        );
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Country\SearchField::getIndexDefinition
     */
    private function getSearchFieldName(CountryTermAggregation $aggregation): string
    {
        switch ($aggregation->getType()) {
            case CountryTermAggregation::TYPE_NAME:
                return 'name';
            case CountryTermAggregation::TYPE_ALPHA_2:
                return 'aplha2';
            case CountryTermAggregation::TYPE_ALPHA_3:
                return 'alpha3';
            case CountryTermAggregation::TYPE_IDC:
                return 'idc';
        }

        throw new InvalidArgumentValue('$aggregation->type', 'Invalid aggregation type: ' . $aggregation->getType());
    }
}
