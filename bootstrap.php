<?php
/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */

require __DIR__ . '/autoload.php';

// для некоторых mysql функций не работающих с БД нужно
// соединение (например mysql_escape_string)
mysql_connect('localhost', 'root', '') or die('Невозможно подключиться к БД');
