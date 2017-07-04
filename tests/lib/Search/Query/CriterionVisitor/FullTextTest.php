<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\FieldType\TextLine\SearchField;
use eZ\Publish\SPI\Search\FieldType\StringField;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\QueryTranslator\WordVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\FullText;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use QueryTranslator\Languages\Galach\Generators;
use QueryTranslator\Languages\Galach\Parser;
use QueryTranslator\Languages\Galach\TokenExtractor\Text;
use QueryTranslator\Languages\Galach\Tokenizer;

/**
 * Test case for FullText criterion visitor.
 *
 * @covers \EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\FullText
 */
class FullTextTest extends TestCase
{
    protected function getFullTextCriterionVisitor(array $fieldTypes = array())
    {
        $fieldNames = array_keys($fieldTypes);
        $fieldNameResolver = $this->getMockBuilder(FieldNameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldNames', 'getFieldTypes'])
            ->getMock();

        $fieldNameResolver
            ->expects($this->any())
            ->method('getFieldNames')
            ->with(
                $this->isInstanceOf(Criterion::class),
                $this->isType('string')
            )
            ->will(
                $this->returnValue($fieldNames)
            );

        $fieldNameResolver
            ->expects($this->any())
            ->method('getFieldTypes')
            ->with(
                $this->isInstanceOf(Criterion::class),
                $this->isType('string')
            )
            ->will(
                $this->returnValue($fieldTypes)
            );

        /** @var \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver */
        return new FullText(
            $fieldNameResolver,
            $this->getTokenizer(),
            $this->getParser(),
            $this->getGenerator()
        );
    }

    /**
     * @return \QueryTranslator\Languages\Galach\Tokenizer
     */
    protected function getTokenizer()
    {
        return new Tokenizer(
            new Text()
        );
    }

    /**
     * @return \QueryTranslator\Languages\Galach\Parser
     */
    protected function getParser()
    {
        return new Parser();
    }

    /**
     * @return \QueryTranslator\Languages\Galach\Generators\ExtendedDisMax
     */
    protected function getGenerator()
    {
        return new Generators\ExtendedDisMax(
            new Generators\Common\Aggregate(
                [
                    new Generators\Lucene\Common\Group(),
                    new Generators\Lucene\Common\LogicalAnd(),
                    new Generators\Lucene\Common\LogicalNot(),
                    new Generators\Lucene\Common\LogicalOr(),
                    new Generators\Lucene\Common\Mandatory(),
                    new Generators\Lucene\Common\Prohibited(),
                    new Generators\Lucene\Common\Phrase(),
                    new Generators\Lucene\Common\Query(),
                    new Generators\Lucene\Common\Tag(),
                    new WordVisitor(),
                    new Generators\Lucene\Common\User(),
                ]
            )
        );
    }

    public function testVisitSimple()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');

        $this->assertEquals(
            "{!edismax v='Hello' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitSimpleMultipleWords()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello World');

        $this->assertEquals(
            "{!edismax v='Hello World' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzy()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;

        $this->assertEquals(
            "{!edismax v='Hello~0.5' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyMultipleWords()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello World');
        $criterion->fuzziness = .5;

        $this->assertEquals(
            "{!edismax v='Hello~0.5 World~0.5' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoost()
    {
        $ftTextLine = new SearchField();
        $visitor = $this->getFullTextCriterionVisitor(
            array(
                'title_1_s' => $ftTextLine,
                'title_2_s' => $ftTextLine,
            )
        );

        $criterion = new Criterion\FullText('Hello');
        $criterion->boost = array('title' => 2);

        $this->assertEquals(
            "{!edismax v='Hello' qf='meta_content__text_t title_1_s^2 title_2_s^2' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoostMultipleWords()
    {
        $ftTextLine = new SearchField();
        $visitor = $this->getFullTextCriterionVisitor(
            array(
                'title_1_s' => $ftTextLine,
                'title_2_s' => $ftTextLine,
            )
        );

        $criterion = new Criterion\FullText('Hello World');
        $criterion->boost = array('title' => 2);

        $this->assertEquals(
            "{!edismax v='Hello World' qf='meta_content__text_t title_1_s^2 title_2_s^2' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoostUnknownField()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');
        $criterion->boost = array(
            'unknown_field' => 2,
        );

        $this->assertEquals(
            "{!edismax v='Hello' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoostUnknownFieldMultipleWords()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello World');
        $criterion->boost = array(
            'unknown_field' => 2,
        );

        $this->assertEquals(
            "{!edismax v='Hello World' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyBoost()
    {
        $stringField = new StringField();
        $visitor = $this->getFullTextCriterionVisitor(
            array(
                'title_1_s' => $stringField,
                'title_2_s' => $stringField,
            )
        );
        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;
        $criterion->boost = array('title' => 2);

        $this->assertEquals(
            "{!edismax v='Hello~0.5' qf='meta_content__text_t title_1_s^2 title_2_s^2' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyBoostMultipleWords()
    {
        $stringField = new StringField();
        $visitor = $this->getFullTextCriterionVisitor(
            array(
                'title_1_s' => $stringField,
                'title_2_s' => $stringField,
            )
        );
        $criterion = new Criterion\FullText('Hello World');
        $criterion->fuzziness = .5;
        $criterion->boost = array('title' => 2);

        $this->assertEquals(
            "{!edismax v='Hello~0.5 World~0.5' qf='meta_content__text_t title_1_s^2 title_2_s^2' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitErrorCorrection()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('OR Hello && (and goodbye)) AND OR AND "as NOT +always');

        $this->assertEquals(
            "{!edismax v='Hello AND (and goodbye) as +always' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }
}
