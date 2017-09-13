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
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase;
use EzSystems\EzPlatformSolrSearchEngine\Query;

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
        $fieldNameResolver = $this->getMockBuilder('\\eZ\\Publish\\Core\\Search\\Common\\FieldNameResolver')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldNames', 'getFieldTypes'))
            ->getMock();

        $fieldNameResolver
            ->expects($this->any())
            ->method('getFieldNames')
            ->with(
                $this->isInstanceOf('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'),
                $this->isType('string')
            )
            ->will(
                $this->returnValue($fieldNames)
            );

        $fieldNameResolver
            ->expects($this->any())
            ->method('getFieldTypes')
            ->with(
                $this->isInstanceOf('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'),
                $this->isType('string')
            )
            ->will(
                $this->returnValue($fieldTypes)
            );

        return new Query\Content\CriterionVisitor\FullText($fieldNameResolver);
    }

    public function testVisitSimple()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');

        $this->assertEquals(
            '(meta_content__text_t:(Hello))',
            $visitor->visit($criterion)
        );
    }

    public function testVisitSimpleMultipleWords()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello World');

        $this->assertEquals(
            '(meta_content__text_t:(Hello World))',
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzy()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;

        $this->assertEquals(
            '(meta_content__text_t:(Hello~0.5))',
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyMultipleWords()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello World');
        $criterion->fuzziness = .5;

        $this->assertEquals(
            '(meta_content__text_t:(Hello~0.5 World~0.5))',
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
            '(meta_content__text_t:(Hello) OR title_1_s:(Hello)^2 OR title_2_s:(Hello)^2)',
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
            '(meta_content__text_t:(Hello World) OR title_1_s:(Hello World)^2 OR title_2_s:(Hello World)^2)',
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
            '(meta_content__text_t:(Hello))',
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
            '(meta_content__text_t:(Hello World))',
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
            '(meta_content__text_t:(Hello~0.5) OR title_1_s:(Hello~0.5)^2 OR title_2_s:(Hello~0.5)^2)',
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
            '(meta_content__text_t:(Hello~0.5 World~0.5) OR title_1_s:(Hello~0.5 World~0.5)^2 OR title_2_s:(Hello~0.5 World~0.5)^2)',
            $visitor->visit($criterion)
        );
    }
}
