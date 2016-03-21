<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the FullText criterion.
 */
class FullText extends CriterionVisitor
{
    /**
     * Field map.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * Create from content type handler and field registry.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     */
    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * Get field type information.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $fieldDefinitionIdentifier
     *
     * @return array
     */
    protected function getSearchFields(Criterion $criterion, $fieldDefinitionIdentifier)
    {
        return $this->fieldNameResolver->getFieldTypes($criterion, $fieldDefinitionIdentifier);
    }

    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @param Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\FullText;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $queries = array(
            'text:' . $criterion->value,
        );

        foreach ($criterion->boost as $field => $boost) {
            $searchFields = $this->getSearchFields($criterion, $field);

            foreach ($searchFields as $name => $fieldType) {
                $queries[] = $name . ':' . $criterion->value . '^' . $boost;
            }
        }

        return '((' . implode(
            ') OR (',
            array_map(
                function ($search) use ($criterion) {
                    return $search . (
                        $criterion->fuzziness < 1 ?
                            sprintf('~%.1f', $criterion->fuzziness) :
                            ''
                        );
                },
                $queries
            )
        ) . '))';
    }
}
