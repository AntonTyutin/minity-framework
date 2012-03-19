<?php
/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */

spl_autoload_register(
    function ($classname)
    {
        $filename = __DIR__ . '/lib/'
            . strtr($classname, '\\', '/')
            . '.php';
        if (file_exists($filename)) {
            include $filename;
        }
    }
);
