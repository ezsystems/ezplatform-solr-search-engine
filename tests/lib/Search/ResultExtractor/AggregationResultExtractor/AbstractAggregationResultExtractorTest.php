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
use PHPUnit\Framework\TestCase;
use stdClass;

abstract class AbstractAggregationResultExtractorTest extends TestCase
{
    protected const EXAMPLE_AGGREGATION_NAME = 'custom_aggregation';
    protected const EXAMPLE_LANGUAGE_FILTER = [];

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor */
    protected $extractor;

    protected function setUp(): void
    {
        $this->extractor = $this->createExtractor();
    }

    abstract protected function createExtractor(): AggregationResultExtractor;

    /**
     * @dataProvider dataProviderForTestCanVisit
     */
    public function testCanVisit(
        Aggregation $aggregation,
        array $languageFilter,
        bool $expectedResult
    ): void {
        $this->assertEquals(
            $expectedResult,
            $this->extractor->canVisit($aggregation, $languageFilter)
        );
    }

    abstract public function dataProviderForTestCanVisit(): iterable;

    /**
     * @dataProvider dataProviderForTestExtract
     */
    public function testExtract(
        Aggregation $aggregation,
        array $languageFilter,
        stdClass $rawData,
        AggregationResult $expectedResult
    ): void {
        $this->assertEquals(
            $expectedResult,
            $this->extractor->extract($aggregation, $languageFilter, $rawData)
        );
    }

    abstract public function dataProviderForTestExtract(): iterable;
}
