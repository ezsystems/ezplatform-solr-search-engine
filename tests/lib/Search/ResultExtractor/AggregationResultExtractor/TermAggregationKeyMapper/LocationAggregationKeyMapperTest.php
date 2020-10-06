<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\LocationAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class LocationAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_LOCATION_IDS = ['2', '54', '47'];

    /** @var \eZ\Publish\API\Repository\LocationService|\PHPUnit\Framework\MockObject\MockObject */
    private $locationService;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\LocationAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->locationService = $this->createMock(LocationService::class);
        $this->mapper = new LocationAggregationKeyMapper($this->locationService);
    }

    public function testMap(): void
    {
        $expectedLocations = $this->createExpectedLocations(self::EXAMPLE_LOCATION_IDS);

        $this->locationService
            ->method('loadLocationList')
            ->with(self::EXAMPLE_LOCATION_IDS)
            ->willReturn($expectedLocations);

        $this->assertEquals(
            array_combine(
                self::EXAMPLE_LOCATION_IDS,
                $expectedLocations
            ),
            $this->mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_LOCATION_IDS
            )
        );
    }

    private function createExpectedLocations(iterable $locationIds): array
    {
        $locations = [];
        foreach ($locationIds as $locationId) {
            $locationId = (int)$locationId;

            $location = $this->createMock(Location::class);
            $location->method('__get')->with('id')->willReturn($locationId);

            $locations[$locationId] = $location;
        }

        return $locations;
    }
}
