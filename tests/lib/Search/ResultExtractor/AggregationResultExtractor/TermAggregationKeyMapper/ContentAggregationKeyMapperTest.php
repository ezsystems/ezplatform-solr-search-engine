<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\ContentAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class ContentAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_CONTENT_IDS = [93, 94, 55];

    /** @var \eZ\Publish\API\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject */
    private $contentService;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\ContentAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->contentService = $this->createMock(ContentService::class);
        $this->mapper = new ContentAggregationKeyMapper($this->contentService);
    }

    public function testMap(): void
    {
        $expectedContentInfos = $this->createExpectedContentInfos(self::EXAMPLE_CONTENT_IDS);

        $this->contentService
            ->method('loadContentInfoList')
            ->with(self::EXAMPLE_CONTENT_IDS)
            ->willReturn($expectedContentInfos);

        $this->assertEquals(
            array_combine(
                self::EXAMPLE_CONTENT_IDS,
                $expectedContentInfos
            ),
            $this->mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_CONTENT_IDS
            )
        );
    }

    private function createExpectedContentInfos(iterable $contentIds): array
    {
        $contentInfos = [];
        foreach ($contentIds as $contentId) {
            $contentId = (int)$contentId;

            $location = $this->createMock(ContentInfo::class);
            $location->method('__get')->with('id')->willReturn($contentId);

            $contentInfos[$contentId] = $location;
        }

        return $contentInfos;
    }
}
