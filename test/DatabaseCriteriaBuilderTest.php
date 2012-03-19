<?php

use Minity\Database\CriteriaBuilder;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class DatabaseCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testFilter()
    {
        $builder = new CriteriaBuilder();

        $builder
            ->field('f1')->equals(10)
            ->andField('f2')->greaterThen(100)
            ->andField('f3')->greaterThenOrEquals(0)
            ->andField('f4')->notEquals(0)
            ->orField('f2')->lessThen(50)
            ->andField('f3')->lessThenOrEquals(0)
            ->andField('f5')->in(array('a', 'b', 'c'));

        $expectedFilter = array(
            array(
                array(
                    'field' => 'f1',
                    'operation' => CriteriaBuilder::OPERATION_EQUALS,
                    'value' => 10
                ),
                array(
                    'field' => 'f2',
                    'operation' => CriteriaBuilder::OPERATION_GREATER_THEN,
                    'value' => 100
                ),
                array(
                    'field' => 'f3',
                    'operation' => CriteriaBuilder::OPERATION_GREATER_OR_EQUALS,
                    'value' => 0
                ),
                array(
                    'field' => 'f4',
                    'operation' => CriteriaBuilder::OPERATION_NOT_EQUALS,
                    'value' => 0
                ),
            ),
            array(
                array(
                    'field' => 'f2',
                    'operation' => CriteriaBuilder::OPERATION_LESS_THEN,
                    'value' => 50
                ),
                array(
                    'field' => 'f3',
                    'operation' => CriteriaBuilder::OPERATION_LESS_OR_EQUALS,
                    'value' => 0
                ),
                array(
                    'field' => 'f5',
                    'operation' => CriteriaBuilder::OPERATION_IN,
                    'value' => array('a', 'b', 'c')
                ),
            ),
        );
        $criteria = $builder->build();
        $this->assertEquals(
            $expectedFilter,
            $criteria['filter']
        );
    }

    public function testOrder()
    {
        $builder = new CriteriaBuilder();
        $expectedOrder = array(
        );
        $criteria = $builder->build();
        $this->assertEquals(
            $expectedOrder,
            $criteria['order']
        );
    }

    public function testLimitAndOffset()
    {
        $builder = new CriteriaBuilder();
        $expectedLimit  = 0;
        $expectedOffset = 0;
        $criteria = $builder->build();
        $this->assertEquals($expectedLimit, $criteria['limit']);
        $this->assertEquals($expectedOffset, $criteria['offset']);
    }

}
