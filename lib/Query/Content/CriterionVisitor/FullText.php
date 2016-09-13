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
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText as FullTextCriterion;

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
        return $criterion instanceof FullTextCriterion;
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
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText $criterion */
        $string = $this->prepareSearchString($criterion);
        $queries = [];

        $queries[] = "text:({$string})";

        foreach ($criterion->boost as $field => $boost) {
            $searchFields = $this->getSearchFields($criterion, $field);

            foreach ($searchFields as $name => $fieldType) {
                $queries[] = "{$name}:({$string})^{$boost}";
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }

    /**
     * Prepares full-text search string.
     *
     * Preparing includes escaping special characters and applying fuzziness to tokens.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText $criterion
     *
     * @return string
     */
    private function prepareSearchString(FullTextCriterion $criterion)
    {
        $tokens = [];
        $fuzziness = '';
        if ($criterion->fuzziness < 1) {
            $fuzziness = sprintf('~%.1f', $criterion->fuzziness);
        }

        foreach ($this->tokenizeString($criterion->value) as $token) {
            // Escaping special characters as fuzziness can't be applied to a phrase (quoted string)
            $tokenEscaped = $this->escapeTerm($token);
            $tokens[] = "{$tokenEscaped}{$fuzziness}";
        }

        return implode(' ', $tokens);
    }

    /**
     * Tokenize string.
     *
     * @param string $string
     *
     * @return string[]
     */
    private function tokenizeString($string)
    {
        return array_filter(array_map('trim', preg_split('(\\p{Z})u', $string)));
    }

    /**
     * Escape a term.
     *
     * We don't escape a wildcard.
     *
     * @link http://lucene.apache.org/core/5_0_0/queryparser/org/apache/lucene/queryparser/classic/package-summary.html#Escaping_Special_Characters
     *
     * @param string $input
     *
     * @return string
     */
    private function escapeTerm($input)
    {
        $pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\?|:|\/|\\\)/';

        return preg_replace($pattern, '\\\$1', $input);
    }
}
