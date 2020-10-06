<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\ContentTypeAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class ContentTypeAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_CONTENT_TYPE_IDS = [1, 2, 3];

    /** @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject */
    private $contentTypeService;

    protected function setUp(): void
    {
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
    }

    public function testMap(): void
    {
        $expectedContentTypes = $this->createContentTypesList(self::EXAMPLE_CONTENT_TYPE_IDS);

        $this->contentTypeService
            ->method('loadContentTypeList')
            ->with(self::EXAMPLE_CONTENT_TYPE_IDS, [])
            ->willReturn($expectedContentTypes);

        $mapper = new ContentTypeAggregationKeyMapper($this->contentTypeService);

        $this->assertEquals(
            array_combine(
                self::EXAMPLE_CONTENT_TYPE_IDS,
                $expectedContentTypes
            ),
            $mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_CONTENT_TYPE_IDS
            )
        );
    }

    private function createContentTypesList(iterable $ids): array
    {
        $contentTypes = [];
        foreach ($ids as $id) {
            $contentType = $this->createMock(ContentType::class);
            $contentType->method('__get')->with('id')->willReturn($id);

            $contentTypes[] = $contentType;
        }

        return $contentTypes;
    }
}
