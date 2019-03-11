<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\FacetBuilderVisitor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\FieldFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\FieldFacet;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;

/**
 * Visits the Field facet builder.
 */
class Field extends FacetBuilderVisitor implements FacetFieldVisitor
{
    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameResolver
     */
    private $fieldNameResolver;

    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
    }

    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        $values = [];
        $totalCount = 0;
        $missingCount = 0;

        /*
         * Data is an array in following format
         *
         * [
         *      [0] => 28   // facet x key
         *      [1] => 31   // facet x count
         *      [2] => 29   // facet x+1 key
         *      [3] => 3    // facet x+1 count
         *      ...
         *      [20] =>     // null indicates total missing to follow
         *      [21] => 287 // total missing
         * ]
         *
         */
        reset($data);
        while ($key = current($data)) {
            $totalCount += $values[$key] = next($data);
            next($data);
        }

        if (current($data) === null) {
            $totalCount += $missingCount = next($data);
        }

        return new FieldFacet([
            'name' => $facetBuilder->name,
            'entries' => $values,
            'missingCount' => $missingCount,
            'totalCount' => $totalCount,
            'otherCount' => $totalCount - $missingCount,
        ]);
    }

    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FieldFacetBuilder;
    }

    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        $parameters = [];

        foreach ($facetBuilder->fieldPaths as $fieldPath) {
            $parts = explode('/', $fieldPath);

            if (count($parts) > 1) {
                $criteria = new Criterion\ContentTypeIdentifier(array_shift($parts));
            } else {
                $criteria = new Criterion\MatchAll();
            }

            $fieldDefinitionIdentifier = array_shift($parts);
            $name = array_shift($parts);

            $fieldTypes = $this->fieldNameResolver->getFieldTypes(
                $criteria,
                $fieldDefinitionIdentifier,
                null,
                $name
            );

            foreach ($fieldTypes as $fieldName => $fieldType) {
                $parameters = array_merge($parameters, [
                    'facet.field' => "{!ex=dt key={$fieldId}}{$fieldName}",
                    "f.{$fieldName}.facet.limit" => $facetBuilder->limit,
                    "f.{$fieldName}.facet.mincount" => $facetBuilder->minCount,
                    "f.{$fieldName}.facet.sort" => $this->getSort($facetBuilder),
                    "f.{$fieldName}.facet.missing" => 'true',
                ]);
                if (isset($facetBuilder->prefix) && $facetBuilder->prefix) {
                    array_merge($parameters, [
                        "f.{$fieldName}.facet.prefix" => '"' . $this->escapeQuote($facetBuilder->prefix, true) . '"',
                    ]);
                }
                if (isset($facetBuilder->contains) && $facetBuilder->contains) {
                    array_merge($parameters, [
                        "f.{$fieldName}.facet.contains" => '"' . $this->escapeQuote($facetBuilder->contains, true) . '"',
                        "f.{$fieldName}.facet.contains.ignoreCase" => $facetBuilder->containsIgnoreCase ? 'true' : 'false',
                    ]);
                }
            }
        }

        return $parameters;
    }

    private function getSort(FieldFacetBuilder $facetBuilder)
    {
        switch ($facetBuilder->sort) {
            case FieldFacetBuilder::COUNT_DESC:
                return 'count';
            case FieldFacetBuilder::TERM_ASC:
                return 'index';
        }

        throw new NotImplementedException('Sort order not supported');
    }

    /**
     * Escapes given $string for wrapping inside single or double quotes.
     *
     * Does not include quotes in the returned string, this needs to be done by the consumer code.
     *
     * @param string $string
     * @param bool   $doubleQuote
     *
     * @return string
     */
    protected function escapeQuote($string, $doubleQuote = false)
    {
        $pattern = ($doubleQuote ? '/("|\\\)/' : '/(\'|\\\)/');

        return preg_replace($pattern, '\\\$1', $string);
    }
}
