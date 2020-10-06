<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationResultExtractor;
use stdClass;

final class TermAggregationResultExtractorTest extends AbstractAggregationResultExtractorTest
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper|\PHPUnit\Framework\MockObject\MockObject */
    private $keyMapper;

    protected function setUp(): void
    {
        $this->keyMapper = $this->createMock(TermAggregationKeyMapper::class);
        $this->keyMapper
            ->method('map')
            ->willReturnCallback(function (
                Aggregation $aggregation,
                array $languageFilter,
                array $keys
            ): array {
                return array_combine($keys, array_map('strtoupper', $keys));
            });

        $this->extractor = $this->createExtractor();
    }

    protected function createExtractor(): AggregationResultExtractor
    {
        return new TermAggregationResultExtractor(
            AbstractTermAggregation::class,
            $this->keyMapper,
        );
    }

    public function dataProviderForTestCanVisit(): iterable
    {
        yield 'true' => [
            $this->createMock(AbstractTermAggregation::class),
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
        $aggregation = $this->createMock(AbstractTermAggregation::class);
        $aggregation->method('getName')->willReturn(self::EXAMPLE_AGGREGATION_NAME);

        yield 'defaults' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            $this->createEmptyRawData(),
            new TermAggregationResult(self::EXAMPLE_AGGREGATION_NAME, []),
        ];

        yield 'typical' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            $this->createTypicalRawData(),
            new TermAggregationResult(
                self::EXAMPLE_AGGREGATION_NAME,
                [
                    new TermAggregationResultEntry('FOO', 10),
                    new TermAggregationResultEntry('BAR', 100),
                    new TermAggregationResultEntry('BAZ', 1000),
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
        $data->buckets = [];
        $data->buckets[] = $this->createRawBucket('foo', 10);
        $data->buckets[] = $this->createRawBucket('bar', 100);
        $data->buckets[] = $this->createRawBucket('baz', 1000);

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
