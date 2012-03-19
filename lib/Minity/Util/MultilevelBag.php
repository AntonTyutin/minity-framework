<?php

namespace Minity\Util;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class MultilevelBag extends Bag
{
    /**
     * @static
     * @param string $filename
     * @return \Minity\Util\MultilevelBag
     */
    public static function createFromFile($filename)
    {
        return new static(include $filename);
    }

    /**
     * @param $what
     * @param mixed $default
     * @return mixed
     */
    public function get($what, $default = null)
    {
        $level = &$this->data;
        foreach (explode('.', $what) as $step) {
            if (!isset($level[$step])) {
                return $default;
            }
            $level = &$level[$step];
        }
        return $level;
    }

    public function has($what)
    {
        $level = &$this->data;
        foreach (explode('.', $what) as $step) {
            if (!isset($level[$step])) {
                return false;
            }
            $level = &$level[$step];
        }
        return true;
    }


    /**
     * @param $what
     * @param $value
     */
    public function set($what, $value)
    {
        $level = &$this->data;
        $prev = null;
        foreach (explode('.', $what) as $step) {
            if (!isset($level[$step])) {
                $level[$step] = array();
            }
            $prev  = &$level;
            $level = &$level[$step];
        }
        $prev[$step] = $value;
    }

    /**
     * @param \Minity\Util\Bag $bag
     */
    public function merge(Bag $bag)
    {
        $this->data = array_replace_recursive(
            $this->data,
            $bag->toArray()
        );
    }
}
