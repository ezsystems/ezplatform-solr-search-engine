<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;

/**
 * Visits the FieldRelation criterion.
 */
class FieldRelation extends Field
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\FieldRelation &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::CONTAINS);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $searchFields = $this->getSearchFields($criterion, $criterion->target);

        if (empty($searchFields)) {
            throw new InvalidArgumentException('$criterion->target', "No searchable fields found for the given criterion target '{$criterion->target}'.");
        }

        $criterionValue = (array)$criterion->value;
        switch ($criterion->operator) {
            case Operator::CONTAINS:
                $op = ' AND ';
                break;
            case Operator::IN:
            default:
                $op = ' OR ';
        }

        $queries = [];
        foreach ($searchFields as $name => $fieldType) {
            $perFieldQueries = [];
            foreach ($criterionValue as $value) {
                $perValueQueries = [];
                $preparedValues = (array)$this->mapSearchFieldvalue($value, $fieldType);
                foreach ($preparedValues as $prepValue) {
                    $perValueQueries[] = $name . ':"'
                        . $this->escapeQuote($this->toString($prepValue), true)
                        . '"';
                }
                // in core, count will always === 1 but can potentially be extended by user code?
                $perFieldQueries[] = count($perValueQueries) === 1
                    ? $perValueQueries[0]
                    : '(' . implode(' AND ', $perValueQueries) . ')';
            }

            // actual operator used here, on per-field basis
            $queries[] = count($perFieldQueries) === 1
                ? $perFieldQueries[0]
                : '(' . implode($op, $perFieldQueries) . ')';
        }

        // note that " OR " is always here to make sure no "class1_attr AND class2_attr" request generated,
        // as it will lead to 100% no results
        return count($queries) === 1
            ? $queries[0]
            : '(' . implode(' OR ', $queries) . ')';
    }
}
