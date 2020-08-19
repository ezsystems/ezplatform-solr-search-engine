<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class UserGroupAggregationKeyMapper implements TermAggregationKeyMapper
{
    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\UserTermAggregation $aggregation
     * @param string[] $keys
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    public function map(AggregationInterface $aggregation, array $languageFilter, array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            try {
                $results[$key] = $this->userService->loadUserGroup((int)$key);
            } catch (NotFoundException | UnauthorizedException $e) {
                // Skip missing user groups
            }
        }

        return $results;
    }
}
