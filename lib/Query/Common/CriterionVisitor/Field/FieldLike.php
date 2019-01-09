<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;

/**
 * Visits the Field criterion.
 */
class FieldLike extends Field
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\Field && $criterion->operator === Operator::LIKE;
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

        $queries = [];
        foreach ($searchFields as $name => $fieldType) {
            $preparedValue = $this->toString($this->mapSearchFieldValue($criterion->value, $fieldType));

            // Check if there is user supplied wildcard or not
            if (strpos($preparedValue, '*') !== false) {
                $queries[] = $name . ':' . $this->escapeExpressions($preparedValue, true);
            } else {
                $queries[] = $name . ':"' . $this->escapeQuote($preparedValue, true) . '"';
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
