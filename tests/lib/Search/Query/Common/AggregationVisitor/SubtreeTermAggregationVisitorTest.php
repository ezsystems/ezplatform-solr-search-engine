<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Location\SubtreeTermAggregation;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\SubtreeTermAggregationVisitor;

final class SubtreeTermAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    private const EXAMPLE_AGGREGATION_NAME = 'custom_aggregation';
    private const EXAMPLE_PATH_STRING = '/1/2/';

    private const EXAMPLE_PATH_STRING_FIELD_NAME = 'path_string_id';
    private const EXAMPLE_LOCATION_ID_FIELD_NAME = 'location_id_id';

    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            new SubtreeTermAggregation(
                self::EXAMPLE_AGGREGATION_NAME,
                self::EXAMPLE_PATH_STRING
            ),
            self::EXAMPLE_LANGUAGE_FILTER,
            true,
        ];

        yield 'false' => [
            $this->createMock(Aggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            false,
        ];
    }

    public function dataProviderForVisit(): iterable
    {
        yield [
            new SubtreeTermAggregation(
                self::EXAMPLE_AGGREGATION_NAME,
                self::EXAMPLE_PATH_STRING
            ),
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'query',
                'q' => 'path_string_id:\/1\/2\/?*',
                'facet' => [
                    'nested' => [
                        'type' => 'terms',
                        'field' => 'location_id_id',
                        'limit' => 10,
                        'mincount' => 1,
                    ],
                ],
            ],
        ];
    }

    protected function createVisitor(): AggregationVisitor
    {
        return new SubtreeTermAggregationVisitor(
            self::EXAMPLE_PATH_STRING_FIELD_NAME,
            self::EXAMPLE_LOCATION_ID_FIELD_NAME
        );
    }
}
