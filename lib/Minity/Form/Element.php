<?php

namespace Minity\Form;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class Element
{
    private $name;

    private $type;

    private $label;

    private $options = array();

    private $constraints = array();

    private $data;

    private $errors = array();

    public function __construct($name, $options = array())
    {
        $this->setName($name);
        $this->setType(isset($options['type']) ? $options['type'] : 'text');
        $this->setLabel(
            isset($options['label'])
                ? $options['label']
                : mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1)
        );
        unset($options['type'], $options['label']);
        $this->setOptions($options);
    }

    public function constraints(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public function isValid()
    {
        return !count($this->errors);
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->validate();
    }

    public function getData()
    {
        return $this->data;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFields()
    {
        return array();
    }

    protected function validate()
    {
        $this->errors = array();
        $value = $this->getData();
        foreach ($this->constraints as $constraint) {
            if (is_callable($constraint)) {
                // $constraint = $constraint;
            } elseif ($constraint && is_string($constraint)
            && method_exists(__CLASS__, 'validate' . $constraint)
        ) {
                $constraint = array(__CLASS__, 'validate' . $constraint);
            } else {
                throw new \RuntimeException(
                    sprintf(
                        'Неизвестный тип ограничения для значения поля "%s"',
                        $this->getName()
                    )
                );
            }
            $error = call_user_func(
                $constraint,
                $value
            );
            if ($error) {
                $this->errors[] = $error;
            }
        }
    }

    public static function validateNotBlank($value)
    {
        return $value !== 0
                && $value !== '0'
                && $value !== null
                && trim($value) == ''
            ? 'должно быть заполнено'
            : '';
    }
}
