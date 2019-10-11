<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;

use eZ\Publish\SPI\Search\FieldType\BooleanField;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper\ContentDocumentNullFields;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;

/**
 * Visits the Field criterion.
 */
class FieldIn extends Field
{
    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    private $fieldNameGenerator;

    /**
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param \eZ\Publish\Core\Search\Common\FieldValueMapper $fieldValueMapper
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(FieldNameResolver $fieldNameResolver, FieldValueMapper $fieldValueMapper, FieldNameGenerator $fieldNameGenerator)
    {
        parent::__construct($fieldNameResolver, $fieldValueMapper);

        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\Field &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ ||
                $criterion->operator === Operator::CONTAINS);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $searchFields = $this->getSearchFields($criterion);

        if (empty($searchFields)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        if ($criterion->value === null) {
            $criterion->value[] = null;
        } else {
            $criterion->value = (array)$criterion->value;
        }
        $queries = array();

        foreach ($searchFields as $name => $fieldType) {
            foreach ($criterion->value as $value) {
                if ($value === null) {
                    $name = $this->fieldNameGenerator->getTypedName(
                        $this->fieldNameGenerator->getName(
                            ContentDocumentNullFields::IS_NULL_NAME,
                            $criterion->target
                        ),
                        new BooleanField()
                    );
                    $queries[] = $name . ':true';
                } else {
                    $preparedValue = $this->escapeQuote(
                        $this->toString(
                            $this->mapSearchFieldValue($value, $fieldType)
                        ),
                        true
                    );
                    $queries[] = $name . ':"' . $preparedValue . '"';
                }
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
