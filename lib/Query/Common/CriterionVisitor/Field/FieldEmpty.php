<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\BooleanField;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper\ContentDocumentEmptyFields;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;

/**
 * Visits the IsFieldEmpty criterion.
 */
final class FieldEmpty extends Field
{
    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    private $fieldNameGenerator;

    public function __construct(
        FieldNameResolver $fieldNameResolver,
        FieldValueMapper $fieldValueMapper,
        FieldNameGenerator $fieldNameGenerator
    ) {
        parent::__construct($fieldNameResolver, $fieldValueMapper);

        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Check if visitor is applicable to current criterion.
     */
    public function canVisit(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\IsFieldEmpty;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        $searchFields = $this->getSearchFields($criterion);

        if (empty($searchFields)) {
            throw new InvalidArgumentException('$criterion->target', "No searchable fields found for the given criterion target '{$criterion->target}'.");
        }

        $criterion->value = (array)$criterion->value;
        $queries = [];

        foreach ($searchFields as $name => $fieldType) {
            foreach ($criterion->value as $value) {
                $name = $this->fieldNameGenerator->getTypedName(
                    $this->fieldNameGenerator->getName(
                        ContentDocumentEmptyFields::IS_EMPTY_NAME,
                        $criterion->target
                    ),
                    new BooleanField()
                );
                $queries[] = $name . ':' . (int) $value;
            }
        }

        return '(' . implode(' OR ', array_unique($queries)) . ')';
    }
}
