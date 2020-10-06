<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\UserMetadataAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class UserMetadataAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_USER_IDS = [1, 2, 3];
    private const EXAMPLE_USER_GROUP_IDS = [1, 2, 3];

    /** @var \eZ\Publish\API\Repository\UserService|\PHPUnit\Framework\MockObject\MockObject */
    private $userService;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\UserMetadataAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->mapper = new UserMetadataAggregationKeyMapper($this->userService);
    }

    /**
     * @dataProvider dataProviderForTestMapUser
     */
    public function testMapForUserKey(Aggregation $aggregation): void
    {
        $this->assertEquals(
            $this->createExpectedResultForUserKey(self::EXAMPLE_USER_IDS),
            $this->mapper->map(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_USER_IDS,
            )
        );
    }

    public function dataProviderForTestMapUser(): iterable
    {
        yield UserMetadataTermAggregation::OWNER => [
            new UserMetadataTermAggregation('owner', UserMetadataTermAggregation::OWNER),
        ];

        yield UserMetadataTermAggregation::MODIFIER => [
            new UserMetadataTermAggregation('modifier', UserMetadataTermAggregation::MODIFIER),
        ];
    }

    public function testMapForUserGroup(): void
    {
        $aggregation = new UserMetadataTermAggregation('group', UserMetadataTermAggregation::GROUP);

        $this->assertEquals(
            $this->createExpectedResultForUserGroupKey(self::EXAMPLE_USER_GROUP_IDS),
            $this->mapper->map(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_USER_GROUP_IDS,
            )
        );
    }

    private function createExpectedResultForUserKey(iterable $userIds): array
    {
        $users = [];
        foreach ($userIds as $i => $userId) {
            $user = $this->createMock(User::class);

            $this->userService
                ->expects($this->at($i))
                ->method('loadUser')
                ->with($userId)
                ->willReturn($user);

            $users[$userId] = $user;
        }

        return $users;
    }

    private function createExpectedResultForUserGroupKey(iterable $userGroupsIds): array
    {
        $users = [];
        foreach ($userGroupsIds as $i => $userGroupId) {
            $user = $this->createMock(UserGroup::class);

            $this->userService
                ->expects($this->at($i))
                ->method('loadUserGroup')
                ->with($userGroupId)
                ->willReturn($user);

            $users[$userGroupId] = $user;
        }

        return $users;
    }
}
