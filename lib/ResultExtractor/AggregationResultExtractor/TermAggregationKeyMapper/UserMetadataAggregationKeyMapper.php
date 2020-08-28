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
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class UserMetadataAggregationKeyMapper implements TermAggregationKeyMapper
{
    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation $aggregation
     * @param string[] $keys
     *
     * @return \eZ\Publish\API\Repository\Values\User\User[]
     */
    public function map(AggregationInterface $aggregation, array $languageFilter, array $keys): array
    {
        $loader = $this->resolveKeyLoader($aggregation);

        $results = [];
        foreach ($keys as $key) {
            try {
                $results[$key] = $loader((int)$key);
            } catch (NotFoundException | UnauthorizedException $e) {
                // Skip missing users / user groups
            }
        }

        return $results;
    }

    private function resolveKeyLoader(AggregationInterface $aggregation): callable
    {
        switch($aggregation->getType()) {
            case UserMetadataTermAggregation::OWNER:
            case UserMetadataTermAggregation::MODIFIER:
                return [$this->userService, 'loadUser'];
            case UserMetadataTermAggregation::GROUP:
                return [$this->userService, 'loadUserGroup'];
        }
    }
}
