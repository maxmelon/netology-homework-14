<?php
function myAutoload($className)
{
    $pathToFile = __DIR__ . '/classes/' . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    include "$pathToFile";
}

function myAutoloadInterface($interfaceName)
{
    $interfacePath = './interfaces/' . $interfaceName . '.php';
    if (file_exists($interfacePath))
    {
        include "$interfacePath";
    }
}

spl_autoload_register('myAutoload');
spl_autoload_register('myAutoloadInterface');