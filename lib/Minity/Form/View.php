<?php

namespace Minity\Form;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class View
{
    private $name;

    private $element;

    private $elements = array();

    public function __construct($name, Element $formElement)
    {
        $this->name    = $name;
        $this->element = $formElement;
        foreach ($formElement->getFields() as $field) {
            $this->elements[$field->getName()] =
                new static($name . '[' . $field->getName() . ']', $field);
        }
    }

    public static function renderForm(View $field)
    {
        return $form = '';
    }

    public static function renderText(View $field)
    {
        return sprintf(
            '<input name="%s" value="%s">',
            htmlspecialchars($field->getName()),
            htmlspecialchars($field->getData())
        );
    }

    public static function renderTextarea(View $field)
    {
        return sprintf(
            '<textarea name="%s">%s</textarea>',
            htmlspecialchars($field->getName()),
            htmlspecialchars($field->getData())
        );
    }

    public static function renderSelect(View $field)
    {
        $options = $field->getOptions();
        if (empty($options['options'])) {
            return '';
        }
        $optionsHtml = '';
        foreach ($options['options'] as $value => $label) {
            $optionsHtml .= sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($value),
                (string)$value == $field->getData() ? ' selected="selected"' : '',
                htmlspecialchars($label)
            );
        }
        return sprintf(
            '<select name="%s">%s</select>',
            htmlspecialchars($field->getName()),
            $optionsHtml
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFields()
    {
        return $this->elements;
    }

    public function getLabel()
    {
        return $this->element->getLabel();
    }

    public function getErrors()
    {
        return $this->element->getErrors();
    }

    public function getData()
    {
        return $this->element->getData();
    }

    public function getOptions()
    {
        return $this->element->getOptions();
    }

    public function getHtml()
    {
        return call_user_func(array(__CLASS__, 'render' . $this->element->getType()), $this);
    }
}
