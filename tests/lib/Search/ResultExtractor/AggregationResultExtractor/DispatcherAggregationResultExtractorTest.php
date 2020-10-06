<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\DispatcherAggregationResultExtractor;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DispatcherAggregationResultExtractorTest extends TestCase
{
    private const EXAMPLE_LANGUAGE_FILTER = [];

    public function testSupportsReturnsTrue(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $dispatcher = new DispatcherAggregationResultExtractor([
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, true),
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
        ]);

        $this->assertTrue($dispatcher->canVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER));
    }

    public function testSupportsReturnsFalse(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $dispatcher = new DispatcherAggregationResultExtractor([
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
        ]);

        $this->assertFalse($dispatcher->canVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER));
    }

    public function testExtract(): void
    {
        $aggregation = $this->createMock(Aggregation::class);
        $data = new stdClass();

        $extractorA = $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false);
        $extractorB = $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, true);
        $extractorC = $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false);

        $dispatcher = new DispatcherAggregationResultExtractor([$extractorA, $extractorB, $extractorC]);

        $expectedResult = $this->createMock(AggregationResult::class);

        $extractorB
            ->method('extract')
            ->with($aggregation, self::EXAMPLE_LANGUAGE_FILTER, $data)
            ->willReturn($expectedResult);

        $this->assertEquals(
            $expectedResult,
            $dispatcher->extract($aggregation, self::EXAMPLE_LANGUAGE_FILTER, $data)
        );
    }

    public function testVisitThrowsNotImplementedException(): void
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage('No result extractor available for aggregation: ');

        $aggregation = $this->createMock(Aggregation::class);

        $dispatcher = new DispatcherAggregationResultExtractor([
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
            $this->createExtractorMockWithCanVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
        ]);

        $dispatcher->extract($aggregation, self::EXAMPLE_LANGUAGE_FILTER, new stdClass());
    }

    private function createExtractorMockWithCanVisit(
        Aggregation $aggregation,
        array $languageFilter,
        bool $supports
    ): AggregationResultExtractor {
        $extractor = $this->createMock(AggregationResultExtractor::class);
        $extractor->method('canVisit')->with($aggregation, $languageFilter)->willReturn($supports);

        return $extractor;
    }
}
