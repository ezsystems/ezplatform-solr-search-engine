<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\LanguageAggregationKeyMapper;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class LanguageAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_LANGUAGE_CODES = [];

    /** @var \eZ\Publish\API\Repository\LanguageService|\PHPUnit\Framework\MockObject\MockObject */
    private $languageService;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\CountryAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->languageService = $this->createMock(LanguageService::class);
        $this->mapper = new LanguageAggregationKeyMapper($this->languageService);
    }

    public function testMap(): void
    {
        $expectedLanguages = $this->configureLanguageServiceMock(self::EXAMPLE_LANGUAGE_CODES);

        $this->languageService
            ->method('loadLanguageListByCode')
            ->with(self::EXAMPLE_LANGUAGE_CODES)
            ->willReturn($expectedLanguages);

        $this->assertEquals(
            array_combine(
                self::EXAMPLE_LANGUAGE_CODES,
                $expectedLanguages
            ),
            $this->mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_LANGUAGE_CODES
            )
        );
    }

    private function configureLanguageServiceMock(iterable $languageCodes): array
    {
        $languages = [];
        foreach ($languageCodes as $languageCode) {
            $language = $this->createMock(Language::class);
            $language->method('__get')->with('languageCode')->willReturn($languageCode);

            $languages[] = $languageCode;
        }

        return $languages;
    }
}
