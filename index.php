<?php
// Autoloader + configuration
require_once("./config/autoload.php");
require_once("./config/siteConfig.php");
require_once("./config/DBConfig.php");

// Default controller
define("DEFAULT_CONTROLLER", "Accueil");

// Start session
session_start();

// Get current mode and action
$mode = DEFAULT_CONTROLLER;
if((isset($_GET['mode'])) && preg_match("/^[a-zA-Z]+$/", $_GET['mode']))
    $mode = ucfirst($_GET['mode']);

// Check current action
$action = "";
if((isset($_GET['action'])) && preg_match("/^[a-zA-Z]+$/", $_GET['action']))
    $action = $_GET['action'];

// Route to a dedicated controller
if(!file_exists("controller/{$mode}Controller.php"))
    Controller::goTo("accueil/error.php");
$className = $mode."Controller";

// Route to a dedicated method
if(method_exists($className, $action))
    $methodName = $className."::".$action;
else {
    Controller::goTo("accueil/error.php");
    // Display an error Page ?
    //$methodName = $className."::index";
}
$methodName();