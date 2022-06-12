<?php

namespace EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field\FieldRelation;
use EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase;

/**
 * Test case for FieldRelation criterion visitor.
 *
 * @covers \EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field\FieldRelation
 */
class FieldRelationTest extends TestCase
{
    public function testCanVisitInvalid()
    {
        $handler = $this->getHandler(1);

        $canVisitNotRelation = $handler->canVisit(
            $this->getMockBuilder(Criterion::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass()
        );
        $this->assertFalse($canVisitNotRelation);
    }

    public function testCanVisitInvalidOperator()
    {
        $handler = $this->getHandler(1);

        $criteria = new Criterion\FieldRelation('', Criterion\Operator::CONTAINS, '');
        $criteria->operator = Criterion\Operator::EQ;

        $canVisitRelationWithIvalidOperator = $handler->canVisit($criteria);
        $this->assertFalse($canVisitRelationWithIvalidOperator);
    }

    public function testCanVisitValid()
    {
        $handler = $this->getHandler(1);

        $canVisitContains = $handler->canVisit(
            new Criterion\FieldRelation('', Criterion\Operator::CONTAINS, '')
        );
        $this->assertTrue($canVisitContains);

        $canVisitIn = $handler->canVisit(
            new Criterion\FieldRelation('', Criterion\Operator::IN, ['1'])
        );
        $this->assertTrue($canVisitIn);
    }

    public function testNotSearchable()
    {
        $this->expectException(InvalidArgumentException::class);

        // 0 mean there is no content type with searchable field_id
        $this->getHandler(0)->visit(
            new Criterion\FieldRelation('not_searchable_field', Criterion\Operator::IN, [5])
        );
    }

    public function testVisitIn()
    {
        // single class, single value
        $criterion = new Criterion\FieldRelation('field_id', Criterion\Operator::IN, [5]);
        $expected = 'class1_field_id:"5"';
        $actual = $this->getHandler(1)->visit($criterion);
        $this->assertEquals($expected, $actual);

        // single class, multi value
        $criterion = new Criterion\FieldRelation('field_id', Criterion\Operator::IN, [5, 6]);
        $expected = '(class1_field_id:"5" OR class1_field_id:"6")';
        $actual = $this->getHandler(1)->visit($criterion);
        $this->assertEquals($expected, $actual);

        // multi class, single value
        $criterion = new Criterion\FieldRelation('field_id', Criterion\Operator::IN, ['1']);
        $expected = '(class1_field_id:"1" OR class2_field_id:"1" OR class3_field_id:"1")';
        $actual = $this->getHandler(3)->visit($criterion);
        $this->assertEquals($expected, $actual);

        // multi class, multi value
        $criterion = new Criterion\FieldRelation('field_id', Criterion\Operator::IN, [3, 4]);
        $expected = '((class1_field_id:"3" OR class1_field_id:"4") OR (class2_field_id:"3" OR class2_field_id:"4"))';
        $actual = $this->getHandler(2)->visit($criterion);
        $this->assertEquals($expected, $actual);
    }

    public function testVisitContains()
    {
        // single class, single value
        $criterion = new Criterion\FieldRelation('field_id', Criterion\Operator::CONTAINS, [5]);
        $expected = 'class1_field_id:"5"';
        $actual = $this->getHandler(1)->visit($criterion);
        $this->assertEquals($expected, $actual);

        // single class, multi value
        $criterion = new Criterion\FieldRelation('field_id', Criterion\Operator::CONTAINS, [5, 6]);
        $expected = '(class1_field_id:"5" AND class1_field_id:"6")';
        $actual = $this->getHandler(1)->visit($criterion);
        $this->assertEquals($expected, $actual);

        // multi class, single value
        $criterion = new Criterion\FieldRelation('field_id', Criterion\Operator::CONTAINS, ['1']);
        $expected = '(class1_field_id:"1" OR class2_field_id:"1" OR class3_field_id:"1")';
        $actual = $this->getHandler(3)->visit($criterion);
        $this->assertEquals($expected, $actual);

        // multi class, multi value
        $criterion = new Criterion\FieldRelation('field_id', Criterion\Operator::CONTAINS, [3, 4]);
        $expected = '((class1_field_id:"3" AND class1_field_id:"4") OR (class2_field_id:"3" AND class2_field_id:"4"))';
        $actual = $this->getHandler(2)->visit($criterion);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param int $numberOfTypesWithField
     * @return FieldRelation
     */
    private function getHandler($numberOfTypesWithField)
    {
        $mockType = $this->getMockForAbstractClass(FieldType::class);
        $fieldTypes = [];
        for ($i = 1; $i < $numberOfTypesWithField + 1; $i++) {
            $fieldTypes["class{$i}_field_id"] = clone $mockType;
        }

        $fieldNames = array_keys($fieldTypes);
        $fieldNameResolver = $this->getMockBuilder(FieldNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fieldNameResolver
            ->method('getFieldNames')
            ->with(
                $this->isInstanceOf(Criterion::class),
                $this->isType('string')
            )
            ->willReturn($fieldNames);
        $fieldNameResolver
            ->method('getFieldTypes')
            ->with(
                $this->isInstanceOf(Criterion::class),
                $this->isType('string')
            )
            ->willReturn($fieldTypes);

        $fieldValueMapper = $this->getMockForAbstractClass(FieldValueMapper::class);
        $fieldValueMapper
            ->method('map')
            ->willReturnCallback(function ($field) {
                /** @var Field $field */
                return $field->value;
            });

        /** @var \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver */
        return new FieldRelation($fieldNameResolver, $fieldValueMapper);
    }
}
