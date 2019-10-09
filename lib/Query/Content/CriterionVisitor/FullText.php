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

use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText as FullTextCriterion;
use QueryTranslator\Languages\Galach\Generators\ExtendedDisMax;
use QueryTranslator\Languages\Galach\Parser;
use QueryTranslator\Languages\Galach\Tokenizer;

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
     * @var \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider
     */
    protected $boostFactorProvider;

    /**
     * @var \QueryTranslator\Languages\Galach\Tokenizer
     */
    protected $tokenizer;

    /**
     * @var \QueryTranslator\Languages\Galach\Parser
     */
    protected $parser;

    /**
     * @var \QueryTranslator\Languages\Galach\Generators\ExtendedDisMax
     */
    protected $generator;

    /**
     * @var int
     */
    protected $maxDepth;

    /**
     * Create from content type handler and field registry.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param \EzSystems\EzPlatformSolrSearchEngine\FieldMapper\BoostFactorProvider $boostFactorProvider
     * @param \QueryTranslator\Languages\Galach\Tokenizer $tokenizer
     * @param \QueryTranslator\Languages\Galach\Parser $parser
     * @param \QueryTranslator\Languages\Galach\Generators\ExtendedDisMax $generator
     * @param int $maxDepth
     */
    public function __construct(
        FieldNameResolver $fieldNameResolver,
        BoostFactorProvider $boostFactorProvider,
        Tokenizer $tokenizer,
        Parser $parser,
        ExtendedDisMax $generator,
        $maxDepth = 0
    ) {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->boostFactorProvider = $boostFactorProvider;
        $this->tokenizer = $tokenizer;
        $this->parser = $parser;
        $this->generator = $generator;
        $this->maxDepth = $maxDepth;
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
     * Check if visitor is applicable to current criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
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
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText $criterion */
        $tokenSequence = $this->tokenizer->tokenize($criterion->value);
        $syntaxTree = $this->parser->parse($tokenSequence);

        $options = [];
        if ($criterion->fuzziness < 1) {
            $options['fuzziness'] = $criterion->fuzziness;
        }

        $queryString = $this->generator->generate($syntaxTree, $options);
        $queryStringEscaped = $this->escapeQuote($queryString);
        $queryFields = $this->getQueryFields($criterion);

        return "{!edismax v='{$queryStringEscaped}' qf='{$queryFields}' uf=-*}";
    }

    private function getQueryFields(Criterion $criterion)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText $criterion */
        $queryFields = ['meta_content__text_t'];

        for ($i = 1; $i <= $this->maxDepth; ++$i) {
            $queryFields[] = "meta_related_content_{$i}__text_t^{$this->getBoostFactorForRelatedContent($i)}";
        }

        foreach ($this->getBoostedFields($criterion) as $name => $boost) {
            $queryFields[] = "{$name}^{$boost}";
        }

        return implode(' ', $queryFields);
    }

    /**
     * Returns boost factor for the related content.
     *
     * @param int $depth
     *
     * @return float
     */
    private function getBoostFactorForRelatedContent(int $depth): float
    {
        return 1.0 / pow(2.0, $depth);
    }

    private function getBoostedFields(Criterion $criterion): array
    {
        $boostedFields = [];

        $configuredBoosting = $this->boostFactorProvider->getContentFieldBoostFactors();
        foreach ($configuredBoosting as $type => $typeBoostFactors) {
            foreach ($typeBoostFactors as $field => $boost) {
                //$name = $this->fieldNameResolver->getFieldName($criterion, $type, $field);
                $name = sprintf('meta_content_%s_%s__text_t', $type, $field);
                if ($name !== null) {
                    $boostedFields[$name] = $boost;
                }
            }
        }

        foreach ($criterion->boost as $field => $boost) {
            $searchFields = $this->getSearchFields($criterion, $field);

            foreach ($searchFields as $name => $fieldType) {
                $boostedFields[$name] = $boost;
            }
        }

        return $boostedFields;
    }
}
