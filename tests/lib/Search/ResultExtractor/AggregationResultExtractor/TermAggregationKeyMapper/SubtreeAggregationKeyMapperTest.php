<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Location\SubtreeTermAggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\SubtreeAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class SubtreeAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_PATH_STRING = '/1/2/54/';

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper|\PHPUnit\Framework\MockObject\MockObject */
    private $locationAggregationKeyMapper;

    protected function setUp(): void
    {
        $this->locationAggregationKeyMapper = $this->createMock(TermAggregationKeyMapper::class);
    }

    public function testMap(): void
    {
        $input = ['1', '2', '54', '55', '56', '57'];

        $exceptedResult = $this->createExpectedLocations([54, 55, 56, 57]);

        $aggregation = new SubtreeTermAggregation('example', self::EXAMPLE_PATH_STRING);
        $languageFilter = AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER;

        $this->locationAggregationKeyMapper
            ->method('map')
            ->with($aggregation, $languageFilter, ['54', '55', '56', '57'])
            ->willReturn($exceptedResult);

        $mapper = new SubtreeAggregationKeyMapper($this->locationAggregationKeyMapper);

        $this->assertEquals(
            $exceptedResult,
            $mapper->map(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                $input,
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
