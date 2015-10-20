<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the criterion tree into a Solr query.
 */
abstract class CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @param Criterion $criterion
     *
     * @return bool
     */
    abstract public function canVisit(Criterion $criterion);

    /**
     * Map field value to a proper Solr representation.
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return string
     */
    abstract public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null);

    /**
     * Get Solr range.
     *
     * Start and end are optional, depending on the respective operator. Pass
     * null in this case. The operator may be one of:
     *
     * - case Operator::GT:
     * - case Operator::GTE:
     * - case Operator::LT:
     * - case Operator::LTE:
     * - case Operator::BETWEEN:
     *
     * @param mixed $operator
     * @param mixed $start
     * @param mixed $end
     *
     * @return string
     */
    protected function getRange($operator, $start, $end)
    {
        $startBrace = '[';
        $startValue = '*';
        $endValue = '*';
        $endBrace = ']';

        $start = '"' . $this->escapeQuote($this->toString($start), true) . '"';
        $end = '"' . $this->escapeQuote($this->toString($end), true) . '"';

        switch ($operator) {
            case Operator::GT:
                $startBrace = '{';
                $endBrace = '}';
                // Intentionally omitted break

            case Operator::GTE:
                $startValue = $start;
                break;

            case Operator::LT:
                $startBrace = '{';
                $endBrace = '}';
                // Intentionally omitted break

            case Operator::LTE:
                $endValue = $end;
                break;

            case Operator::BETWEEN:
                $startValue = $start;
                $endValue = $end;
                break;

            default:
                throw new \RuntimeException("Unknown operator: $operator");
        }

        return "$startBrace$startValue TO $endValue$endBrace";
    }

    /**
     * Converts given $value to the appropriate Solr string representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function toString($value)
    {
        switch (gettype($value)) {
            case 'boolean':
                return ($value ? 'true' : 'false');

            default:
                return (string)$value;
        }
    }

    /**
     * Escapes given $string for wrapping inside single or double quotes.
     *
     * Does not include quotes in the returned string, this needs to be done by the consumer code.
     *
     * @param string $string
     * @param bool $doubleQuote
     *
     * @return string
     */
    protected function escapeQuote($string, $doubleQuote = false)
    {
        $pattern = ($doubleQuote ? '/("|\\\)/' : '/(\'|\\\)/');

        return preg_replace($pattern, '\\\$1', $string);
    }
}
