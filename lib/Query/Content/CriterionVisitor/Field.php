<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use EzSystems\EzPlatformSolrSearchEngine\FieldValueMapper;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Search\Field as SearchField;
use eZ\Publish\SPI\FieldType\Indexable;

/**
 * Visits the Field criterion.
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
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldValueMapper
     */
    protected $fieldValueMapper;

    /**
     * Create from content type handler and field registry.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldValueMapper $fieldValueMapper
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
     * @return array array of field types indexed by name.
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
     * @param eZ\Publish\SPI\FieldType\Indexable $searchFieldType
     * @param mixed $value
     *
     * @return mixed
     */
    protected function mapSearchFieldValue(Indexable $searchFieldType, $value)
    {
        $definition = $searchFieldType->getIndexDefinition();
        if ($searchFieldType instanceof \eZ\Publish\Core\FieldType\Selection\SearchField) {
            $matchField = 'selected_option_value';
        } else {
            $matchField = $searchFieldType->getDefaultMatchField();
        }

        $fieldType = $definition[$matchField];
        // convert core fieldtype search into a SPI/Search/Field to use in FieldValueMapper
        $searchField = new SearchField('field', $value, $fieldType);

        $value = $this->fieldValueMapper->map($searchField);

        return current((array)$value);
    }
}
