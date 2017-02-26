<?php
/**
 * Created by PhpStorm.
 * User: tingle
 * Date: 25.02.17
 * Time: 23:28
 */

spl_autoload_register(function ($className) {
    $class = str_replace('\\', '/', $className) . '.php';
    require_once($class);
});
