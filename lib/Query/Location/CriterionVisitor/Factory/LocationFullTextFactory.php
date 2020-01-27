<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Query\Location\CriterionVisitor\Factory;

use EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Factory\FullTextFactoryAbstract;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Location\CriterionVisitor\FullText;

/**
 * Factory for FullText Criterion Visitor.
 *
 * @see \EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\FullText
 *
 * @internal
 */
final class LocationFullTextFactory extends FullTextFactoryAbstract
{
    /**
     * Create FullText Criterion Visitor.
     *
     * @return \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor|\EzSystems\EzPlatformSolrSearchEngine\Query\Location\CriterionVisitor\FullText
     */
    public function createCriterionVisitor(): CriterionVisitor
    {
        return new FullText(
            $this->fieldNameResolver,
            $this->tokenizer,
            $this->parser,
            $this->generator,
            $this->indexingDepthProvider->getMaxDepth()
        );
    }
}
