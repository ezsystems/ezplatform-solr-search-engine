<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationResultExtractor;
use stdClass;

final class RangeAggregationResultExtractorTest extends AbstractAggregationResultExtractorTest
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper|\PHPUnit\Framework\MockObject\MockObject */
    private $keyMapper;

    protected function setUp(): void
    {
        $this->keyMapper = $this->createMock(RangeAggregationKeyMapper::class);
        $this->keyMapper
            ->method('map')
            ->willReturnCallback(function (
                Aggregation $aggregation,
                array $languageFilter,
                string $key
            ): ?string {
                return $key !== '*' ? $key : null;
            });

        $this->extractor = $this->createExtractor();
    }

    protected function createExtractor(): AggregationResultExtractor
    {
        return new RangeAggregationResultExtractor(AbstractRangeAggregation::class, $this->keyMapper);
    }

    public function dataProviderForTestCanVisit(): iterable
    {
        yield 'true' => [
            $this->createMock(AbstractRangeAggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            true,
        ];

        yield 'false' => [
            $this->createMock(Aggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            false,
        ];
    }

    public function dataProviderForTestExtract(): iterable
    {
        $aggregation = $this->createMock(AbstractRangeAggregation::class);
        $aggregation->method('getName')->willReturn(self::EXAMPLE_AGGREGATION_NAME);

        yield 'default' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            $this->createEmptyRawData(),
            new RangeAggregationResult(self::EXAMPLE_AGGREGATION_NAME, []),
        ];

        yield 'typical' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            $this->createTypicalRawData(),
            new RangeAggregationResult(
                self::EXAMPLE_AGGREGATION_NAME,
                [
                    new RangeAggregationResultEntry(new Range(null, '10'), 10),
                    new RangeAggregationResultEntry(new Range('10', '100'), 100),
                    new RangeAggregationResultEntry(new Range('100', null), 1000),
                ]
            ),
        ];
    }

    private function createEmptyRawData(): stdClass
    {
        $data = new stdClass();
        $data->buckets = [];

        return $data;
    }

    private function createTypicalRawData(): stdClass
    {
        $data = new stdClass();
        $data->{'*_10'} = $this->createRawBucket('*_10', 10);
        $data->{'10_100'} = $this->createRawBucket('10_100', 100);
        $data->{'100_*'} = $this->createRawBucket('100_*', 1000);

        return $data;
    }

    private function createRawBucket(string $val, int $count): stdClass
    {
        $bucket = new stdClass();
        $bucket->val = $val;
        $bucket->count = $count;

        return $bucket;
    }
}
