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
use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Search\Field as SearchField;
use eZ\Publish\SPI\Search\FieldType;

/**
 * Base class for Field criterion visitors.
 *
 * @api
 */
abstract class Field extends CriterionVisitor
{
    /**
     * Field map.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldValueMapper
     */
    protected $fieldValueMapper;

    /**
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param \eZ\Publish\Core\Search\Common\FieldValueMapper $fieldValueMapper
     */
    public function __construct(FieldNameResolver $fieldNameResolver, FieldValueMapper $fieldValueMapper)
    {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->fieldValueMapper = $fieldValueMapper;
    }

    /**
     * Get array of search fields.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\SPI\Search\FieldType[] Array of field types indexed by name.
     */
    protected function getSearchFields(Criterion $criterion)
    {
        return $this->fieldNameResolver->getFieldTypes(
            $criterion,
            $criterion->target
        );
    }

    /**
     * Map search field value to solr value using FieldValueMapper.
     *
     * @param mixed $value
     * @param \eZ\Publish\SPI\Search\FieldType $searchFieldType
     *
     * @return mixed
     */
    protected function mapSearchFieldValue($value, FieldType $searchFieldType = null)
    {
        if (null === $searchFieldType) {
            return $value;
        }

        $searchField = new SearchField('field', $value, $searchFieldType);
        $value = (array)$this->fieldValueMapper->map($searchField);

        return current($value);
    }
}
