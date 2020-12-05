<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\SortClauseVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;

final class ContentTranslatedName extends SortClauseVisitor
{
    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\ContentTranslatedName;
    }

    public function visit(SortClause $sortClause): string
    {
        return 'meta_content__name_s' . $this->getDirection($sortClause);
    }
}