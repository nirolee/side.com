<?php

function classLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = '../core/src'. DIRECTORY_SEPARATOR . $path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoader');