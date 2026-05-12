<?php

spl_autoload_register(function($class){

    $paths = [
        "models/$class.php",
        "controllers/$class.php",
        "config/$class.php"
    ];

    foreach($paths as $path){

        if(file_exists($path)){
            require_once $path;
        }

    }

});