<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\MapLocation;

use EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\MapLocation;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the MapLocationDistance criterion.
 */
class MapLocationDistanceRange extends MapLocation
{
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
            $criterion instanceof Criterion\MapLocationDistance &&
            ($criterion->operator === Operator::LT ||
              $criterion->operator === Operator::LTE ||
              $criterion->operator === Operator::GT ||
              $criterion->operator === Operator::GTE ||
              $criterion->operator === Operator::BETWEEN);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $criterion->value = (array)$criterion->value;

        $start = $criterion->value[0];
        $end = isset($criterion->value[1]) ? $criterion->value[1] : 63510;

        if (($criterion->operator === Operator::LT) ||
            ($criterion->operator === Operator::LTE)) {
            $end = $start;
            $start = null;
        }

        $searchFields = $this->getSearchFields(
            $criterion,
            $criterion->target,
            $this->fieldTypeIdentifier,
            $this->fieldName
        );

        if (empty($searchFields)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;

        $queries = array();
        foreach ($searchFields as $name => $fieldType) {
            // @todo in future it should become possible to specify ranges directly on the filter (donut shape)
            $query = sprintf('{!geofilt sfield=%s pt=%F,%F d=%s}', $name, $location->latitude, $location->longitude, $end);
            if ($start !== null) {
                $query = sprintf("{!frange l=%F}{$query}", $start);
            }

            $queries[] = "{$query} AND {$name}_0_coordinate:[* TO *]";
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
