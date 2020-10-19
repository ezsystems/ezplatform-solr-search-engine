<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\NestedAggregationResultExtractor;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NestedAggregationResultExtractorTest extends TestCase
{
    private const EXAMPLE_NESTED_RESULT_KEY = 'foo';

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor|\PHPUnit\Framework\MockObject\MockObject */
    private $innerResultExtractor;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\NestedAggregationResultExtractor */
    private $resultExtractor;

    protected function setUp(): void
    {
        $this->innerResultExtractor = $this->createMock(AggregationResultExtractor::class);
        $this->resultExtractor = new NestedAggregationResultExtractor(
            $this->innerResultExtractor,
            self::EXAMPLE_NESTED_RESULT_KEY
        );
    }

    public function testCanVisit(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $this->innerResultExtractor
            ->expects($this->once())
            ->method('canVisit')
            ->with($aggregation, AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER)
            ->willReturn(true);

        $this->assertTrue(
            $this->resultExtractor->canVisit(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER
            )
        );
    }

    public function testExtract(): void
    {
        $expectedResult = $this->createMock(AggregationResult::class);

        $data = new stdClass();
        $data->buckets = [/* Some data */];

        $aggregation = $this->createMock(Aggregation::class);

        $this->innerResultExtractor
            ->expects($this->once())
            ->method('extract')
            ->with(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                $data
            )
            ->willReturn($expectedResult);

        $wrappedData = new stdClass();
        $wrappedData->{self::EXAMPLE_NESTED_RESULT_KEY} = $data;

        $this->assertEquals(
            $expectedResult,
            $this->resultExtractor->extract(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                $wrappedData
            )
        );
    }
}
