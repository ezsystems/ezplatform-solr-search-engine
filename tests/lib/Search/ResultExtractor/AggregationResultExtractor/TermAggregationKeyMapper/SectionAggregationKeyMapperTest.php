<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Section;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\SectionAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class SectionAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_SECTION_IDS = [1, 2, 3];

    /** @var \eZ\Publish\API\Repository\SectionService|\PHPUnit\Framework\MockObject\MockObject */
    private $sectionService;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\SectionAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->sectionService = $this->createMock(SectionService::class);
        $this->mapper = new SectionAggregationKeyMapper($this->sectionService);
    }

    public function testMap(): void
    {
        $expectedSections = $this->configureSectionServiceMock(self::EXAMPLE_SECTION_IDS);

        $this->assertEquals(
            $expectedSections,
            $this->mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_SECTION_IDS
            )
        );
    }

    private function configureSectionServiceMock(iterable $sectionIds): array
    {
        $sections = [];
        foreach ($sectionIds as $i => $sectionId) {
            $section = $this->createMock(Section::class);

            $this->sectionService
                ->expects($this->at($i))
                ->method('loadSection')
                ->with($sectionId)
                ->willReturn($section);

            $sections[$sectionId] = $section;
        }

        return $sections;
    }
}
