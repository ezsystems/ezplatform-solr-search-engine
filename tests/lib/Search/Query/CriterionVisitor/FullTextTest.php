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

use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformSolrSearchEngine\Query;

/**
 * Test case for FullText criterion visitor.
 *
 * @covers \EzSystems\EzPlatformSolrSearchEngine\Content\CriterionVisitor\FullText
 */
class FullTextTest extends TestCase
{
    protected function getFullTextCriterionVisitor(array $fieldTypes = array())
    {
        $fieldNames = array_keys($fieldTypes);
        $fieldNameResolver = $this->getMock(
            '\\eZ\\Publish\\Core\\Search\\Common\\FieldNameResolver',
            array('getFieldNames', 'getFieldTypes'),
            array(),
            '',
            false
        );

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
            '((text:Hello))',
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzy()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;

        $this->assertEquals(
            '((text:Hello~0.5))',
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoost()
    {
        $ftTextLine = new \eZ\Publish\Core\FieldType\TextLine\SearchField();
        $visitor = $this->getFullTextCriterionVisitor(
            array(
                'title_1_s' => $ftTextLine,
                'title_2_s' => $ftTextLine,
            )
        );

        $criterion = new Criterion\FullText('Hello');
        $criterion->boost = array('title' => 2);

        $this->assertEquals(
            '((text:Hello) OR (title_1_s:Hello^2) OR (title_2_s:Hello^2))',
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
            '((text:Hello))',
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyBoost()
    {
        $ftTextLine = new \eZ\Publish\Core\FieldType\TextLine\SearchField();
        $visitor = $this->getFullTextCriterionVisitor(
            array(
                'title_1_s' => $ftTextLine,
                'title_2_s' => $ftTextLine,
            )
        );
        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;
        $criterion->boost = array('title' => 2);

        $this->assertEquals(
            '((text:Hello~0.5) OR (title_1_s:Hello^2~0.5) OR (title_2_s:Hello^2~0.5))',
            $visitor->visit($criterion)
        );
    }
}
