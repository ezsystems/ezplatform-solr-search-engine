<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\SolrSearchEngine\Query\Location\CriterionVisitor;

use EzSystems\SolrSearchEngine\Query\Content\CriterionVisitor\FullText as ContentFullText;
use EzSystems\SolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the FullText criterion.
 */
class FullText extends ContentFullText
{
    /**
     * Map field value to a proper Solr representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \EzSystems\SolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $condition = $this->escapeQuote(parent::visit($criterion, $subVisitor));

        return "{!child of='document_type_id:content' v='document_type_id:content AND {$condition}'}";
    }
}
