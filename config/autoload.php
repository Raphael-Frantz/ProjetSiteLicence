<?php
spl_autoload_register(function ($class) {
        if(@strpos($class, "Model") !== FALSE) {
            if(file_exists("model/$class.php"))
                require_once("model/$class.php");
        }
        elseif(@strpos($class, "Controller") !== FALSE) {
            if(file_exists("controller/$class.php")) {
                require_once("controller/$class.php");
            }
        }
        else {
            if(file_exists("class/$class.php"))
                require_once("class/$class.php");
        }
    }, true);