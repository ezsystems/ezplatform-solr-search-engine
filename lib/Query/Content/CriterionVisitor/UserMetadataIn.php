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
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the UserMetadata criterion.
 */
class UserMetadataIn extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @param Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\UserMetadata &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
              $criterion->operator === Operator::EQ);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        switch ($criterion->target) {
            case Criterion\UserMetadata::MODIFIER:
                $solrField = 'creator_id';
                break;
            case Criterion\UserMetadata::OWNER:
                $solrField = 'owner_id';
                break;
            case Criterion\UserMetadata::GROUP:
                $solrField = 'owner_user_group_mid';
                break;

            default:
                throw new NotImplementedException(
                    'No visitor available for target: ' . $criterion->target . ' with operator: ' . $criterion->operator
                );
        }

        return '(' .
            implode(
                ' OR ',
                array_map(
                    function ($value) use ($solrField) {
                        return "{$solrField}:\"{$value}\"";
                    },
                    $criterion->value
                )
            ) .
            ')';
    }
}
