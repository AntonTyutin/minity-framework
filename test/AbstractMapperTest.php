<?php

use Minity\Database\CriteriaBuilder;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class AbstractMapperTest extends \PHPUnit_Framework_TestCase
{

    public function testGetById()
    {
        $id = 10;
        $expectedFilter = array(
            array(
                array(
                    'field' => '_id',
                    'operation' => CriteriaBuilder::OPERATION_EQUALS,
                    'value' => $id
                ),
            ),
        );
        $mapper = $this->getMockBuilder('Minity\Database\AbstractMapper')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mapper->expects($this->once())
            ->method('find')
            ->with(
                array(
                     'filter' => $expectedFilter,
                     'order'  => array(),
                     'limit'  => 1,
                     'offset' => 0
                )
            )
            ->will($this->returnValue(array('bingo')));
        $this->assertEquals('bingo', $mapper->getById($id));
    }

}
