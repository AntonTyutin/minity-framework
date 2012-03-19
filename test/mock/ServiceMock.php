<?php

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class ServiceMock
{
    public $arguments;

    function __construct()
    {
        $this->arguments = func_get_args();
    }

}
