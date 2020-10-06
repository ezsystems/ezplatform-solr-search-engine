<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\ContentTypeGroupAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ContentTypeGroupAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_CONTENT_TYPE_GROUPS_IDS = ['1', '2', '3'];

    /** @var MockObject */
    private $contentTypeService;

    protected function setUp(): void
    {
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
    }

    public function testMap(): void
    {
        $expectedContentTypesGroups = $this->createExpectedLanguages();

        $mapper = new ContentTypeGroupAggregationKeyMapper($this->contentTypeService);

        $this->assertEquals(
            $expectedContentTypesGroups,
            $mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_CONTENT_TYPE_GROUPS_IDS
            )
        );
    }

    private function createContentTypeGroupWithIds(int $id): ContentTypeGroup
    {
        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $contentTypeGroup->method('__get')->with('id')->willReturn($id);

        return $contentTypeGroup;
    }

    private function createExpectedLanguages(): array
    {
        $expectedContentTypesGroups = [];

        foreach (self::EXAMPLE_CONTENT_TYPE_GROUPS_IDS as $i => $id) {
            $contentTypeGroup = $this->createContentTypeGroupWithIds((int)$id);

            $this->contentTypeService
                ->expects($this->at($i))
                ->method('loadContentTypeGroup')
                ->with((int)$id, [])
                ->willReturn($contentTypeGroup);

            $expectedContentTypesGroups[$id] = $contentTypeGroup;
        }

        return $expectedContentTypesGroups;
    }
}
