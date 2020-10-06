<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\ObjectStateAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class ObjectStateAggregationKeyMapperTest extends TestCase
{
    /** @var \eZ\Publish\API\Repository\ObjectStateService|\PHPUnit\Framework\MockObject\MockObject */
    private $objectStateService;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\ObjectStateAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->objectStateService = $this->createMock(ObjectStateService::class);
        $this->mapper = new ObjectStateAggregationKeyMapper($this->objectStateService);
    }

    public function testMap(): void
    {
        $expectedObjectStates = array_combine(
            ['ez_lock:unlocked', 'ez_lock:locked'],
            $this->configureObjectStateService('ez_lock', ['unlocked', 'locked'])
        );

        $this->assertEquals(
            $expectedObjectStates,
            $this->mapper->map(
                new ObjectStateTermAggregation('aggregation', 'ez_lock'),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                ['ez_lock:unlocked', 'ez_lock:locked']
            )
        );
    }

    private function configureObjectStateService(
        string $objectStateGroupIdentifier,
        iterable $objectStateIdentifiers
    ): array {
        $objectStateGroup = $this->createMock(ObjectStateGroup::class);

        $this->objectStateService
            ->method('loadObjectStateGroupByIdentifier')
            ->with($objectStateGroupIdentifier)
            ->willReturn($objectStateGroup);

        $expectedObjectStates = [];
        foreach ($objectStateIdentifiers as $i => $objectStateIdentifier) {
            $objectState = $this->createMock(ObjectState::class);

            $this->objectStateService
                ->expects($this->at($i + 1))
                ->method('loadObjectStateByIdentifier')
                ->with($objectStateGroup, $objectStateIdentifier, [])
                ->willReturn($objectState);

            $expectedObjectStates[] = $objectState;
        }

        return $expectedObjectStates;
    }
}
