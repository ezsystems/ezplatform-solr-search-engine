<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
use PHPUnit\Framework\TestCase;

abstract class AbstractAggregationVisitorTest extends TestCase
{
    protected const EXAMPLE_LANGUAGE_FILTER = [
        'languageCode' => 'eng-gb',
    ];

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor */
    protected $visitor;

    /** @var \EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor|\PHPUnit\Framework\MockObject\MockObject */
    protected $dispatcherVisitor;

    protected function setUp(): void
    {
        $this->visitor = $this->createVisitor();
        $this->dispatcherVisitor = $this->createMock(AggregationVisitor::class);
    }

    abstract protected function createVisitor(): AggregationVisitor;

    /**
     * @dataProvider dataProviderForCanVisit
     */
    final public function testCanVisit(
        AggregationInterface $aggregation,
        array $languageFilter,
        bool $expectedValue
    ): void {
        $this->assertEquals(
            $expectedValue,
            $this->visitor->canVisit($aggregation, $languageFilter)
        );
    }

    abstract public function dataProviderForCanVisit(): iterable;

    /**
     * @dataProvider dataProviderForVisit
     */
    final public function testVisit(
        AggregationInterface $aggregation,
        array $languageFilter,
        array $expectedResult
    ): void {
        $this->configureMocksForTestVisit($aggregation, $languageFilter, $expectedResult);

        $this->assertEquals(
            $expectedResult,
            $this->visitor->visit($this->dispatcherVisitor, $aggregation, $languageFilter)
        );
    }

    abstract public function dataProviderForVisit(): iterable;

    protected function configureMocksForTestVisit(
        AggregationInterface $aggregation,
        array $languageFilter,
        array $expectedResult
    ): void {
        // Overwrite in parent class to configure additional mocks
    }
}