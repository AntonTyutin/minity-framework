<?php

namespace Minity\Form;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class Form extends Element
{

    /**
     * Коллекция полей формы
     * @var \Minity\Form\Element[]
     */
    protected $elements;

    public function __construct()
    {
        parent::__construct('form', array());
        $this->elements = new \ArrayObject();
    }


    public function setData($data)
    {
        parent::setData($data);
        foreach ($data as $field => $value) {
            if ($this->has($field)) {
                $this->get($field)->setData($value);
            }
        }
    }

    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        foreach ($this->elements as $element) {
            if (!$element->isValid()) {
                return false;
            }
        }
        return true;
    }

    public function add(
        $name,
        $options = array())
    {
        $element = new Element($name, $options);
        $this->elements[$name] = $element;
        return $element;
    }

    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \OutOfRangeException(
                sprintf('Попытка получить несуществующее поле "%s"', $name)
            );
        }
        return $this->elements[$name];
    }

    public function has($name)
    {
        return isset($this->elements[$name]);
    }

    public function getFields()
    {
        return $this->elements;
    }

    public function getView()
    {
        return new View($this->getName(), $this);
    }
}
