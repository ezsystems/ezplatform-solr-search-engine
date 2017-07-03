<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\QueryTranslator;

use QueryTranslator\Languages\Galach\Generators\Common\Visitor;
use QueryTranslator\Languages\Galach\Generators\Lucene\Common\WordBase;
use QueryTranslator\Values\Node;

/**
 * Word Node Visitor implementation.
 */
class WordVisitor extends WordBase
{
    public function visit(Node $node, Visitor $subVisitor = null, $options = null)
    {
        $word = parent::visit($node, $subVisitor, $options);

        if (isset($options['word_proximity'])) {
            $fuzziness = sprintf('~%.1f', $options['word_proximity']);
            $word .= $fuzziness;
        }

        return $word;
    }

    /**
     * {@inheritdoc}
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
     *
     * Note: additionally to what is defined above we also escape blank space,
     * and we don't escape an asterisk.
     */
    protected function escapeWord($string)
    {
        return preg_replace(
            '/(\\+|-|&&|\\|\\||!|\\(|\\)|\\{|}|\\[|]|\\^|"|~|\\?|:|\\/|\\\\| )/',
            '\\\\$1',
            $string
        );
    }
}
