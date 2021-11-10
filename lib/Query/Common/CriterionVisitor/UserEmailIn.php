<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Ibexa\Solr\FieldMapper\ContentFieldMapper\UserDocumentFields;

final class UserEmailIn extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        $operator = $criterion->operator ?? Operator::IN;
        $supportedOperators = [Operator::IN, Operator::EQ];

        return
            $criterion instanceof Criterion\UserEmail
            && in_array($operator, $supportedOperators, true);
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        return sprintf(
            '(%s)',
            implode(
                ' OR ',
                array_map(
                    static function (string $value): string {
                        return 'user_email_s:"' . hash(UserDocumentFields::HASHING_ALGORITHM, $value) . '"';
                    },
                    (array) $criterion->value
                )
            )
        );
    }
}
