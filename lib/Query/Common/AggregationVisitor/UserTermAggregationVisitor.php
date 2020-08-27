<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\UserTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

final class UserTermAggregationVisitor extends AbstractTermAggregationVisitor
{
    public function canVisit(AggregationInterface $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof UserTermAggregation;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\UserTermAggregation $aggregation
     */
    protected function getTargetField(AbstractTermAggregation $aggregation): string
    {
        switch ($aggregation->getType()) {
            case UserTermAggregation::OWNER:
                return 'content_owner_user_id_id';
            case UserTermAggregation::GROUP:
                return 'content_owner_user_group_ids_mid';
            case UserTermAggregation::MODIFIER:
                return 'content_version_creator_user_id_id';
            default:
                throw new InvalidArgumentException('$type', 'Unsupported UserTermAggregation type: ' . $aggregation->getType());
        }
    }
}