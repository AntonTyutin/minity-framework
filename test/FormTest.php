<?php

use Minity\Form\Form;

/**
 * Test class for Form.
 * Generated by PHPUnit on 2012-03-15 at 14:40:25.
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAndGetData()
    {
        $form = new Form();
        $form->add('field2');
        $data = array(
            'field1' => 'qwrwqtr',
            'field2' => 'v1',
            'field3' => 'ewwttywe',
        );
        $form->setData($data);
        $this->assertEquals($data, $form->getData());
        $this->assertEquals($data['field2'], $form->get('field2')->getData());
    }

    public function testSetAndGetField()
    {
        $form = new Form();
        $form->add('field1');
        $form->add(
            'field2',
            array(
                'type'    => 'select',
                'options' => array(
                    'v1' => 'opt1',
                    'v2' => 'opt2',
                )
            )
        );
        $field = $form->get('field1');
        $this->assertInstanceOf('Minity\Form\Element', $field);

        $field = $form->get('field2');
        $this->assertInstanceOf('Minity\Form\Element', $field);
        $this->assertEquals('select', $field->getType());
        $this->assertEquals('Field2', $field->getLabel());
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testGetUnexistentField()
    {
        $form = new Form();
        $form->get('Unexistent');
    }

    public function testValidate()
    {
        $form = new Form();
        $form->constraints(array(function($value) { return ''; }));
        $form->add('field')->constraints(array(function($value) { return 'Error'; }));
        $form->setData(array('field' => '1'));
        $this->assertFalse($form->isValid());

        $form = new Form();
        $form->constraints(array(function($value) { return ''; }));
        $form->add('field')->constraints(array(function($value) { return ''; }));
        $form->setData(array('field' => '1'));
        $this->assertTrue($form->isValid());

        $form = new Form();
        $form->constraints(array(function($value) { return 'Error'; }));
        $form->add('field')->constraints(array(function($value) { return ''; }));
        $form->setData(array('field' => '1'));
        $this->assertFalse($form->isValid());
    }
}
