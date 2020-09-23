<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor\AggregationFieldResolver;

use eZ\Publish\API\Repository\Values\Content\Query\AggregationInterface;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver\SearchFieldAggregationFieldResolver;
use PHPUnit\Framework\TestCase;

final class SearchFieldAggregationFieldResolverTest extends TestCase
{
    public function testResolveTargetField(): void
    {
        $aggregation = $this->createMock(AggregationInterface::class);

        $aggregationFieldResolver = new SearchFieldAggregationFieldResolver('custom_field_id');

        $this->assertEquals(
            'custom_field_id',
            $aggregationFieldResolver->resolveTargetField($aggregation)
        );
    }
}