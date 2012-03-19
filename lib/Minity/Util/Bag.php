<?php

namespace Minity\Util;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class Bag
{
    /**
     * @var array
     */
    protected $data = array();

    function __construct(array $data = array())
    {
        $this->data = $data;
    }


    public function get($what, $default = null)
    {
        return isset($this->data[$what])
            ? $this->data[$what]
            : $default;
    }

    public function has($what)
    {
        return isset($this->data[$what]);
    }


    /**
     * @param $what
     * @param $value
     */
    public function set($what, $value)
    {
        $this->data[$what] = $value;
    }

    /**
     * @param \Minity\Util\Bag $bag
     */
    public function merge(Bag $bag)
    {
        $this->data = array_replace(
            $this->data,
            $bag->toArray()
        );
    }

    /**
     * @param array $array
     */
    public function fromArray(array $array)
    {
        $this->data = $array;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}
