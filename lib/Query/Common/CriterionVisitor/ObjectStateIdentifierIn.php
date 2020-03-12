<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

class ObjectStateIdentifierIn extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        return
            $criterion instanceof Criterion\ObjectStateIdentifier &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        $target = $criterion->target ?? '*';

        return sprintf(
            '(%s)',
            implode(
                ' OR ',
                array_map(
                    function (string $value) use ($target) {
                        return sprintf(
                            'content_object_state_identifiers_ms:%s',
                            $this->escapeExpressions("{$target}:{$value}", true)
                        );
                    },
                    (array)$criterion->value
                )
            )
        );
    }
}
