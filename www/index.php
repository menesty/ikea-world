<?php
error_reporting(E_ALL);
define('PHP_INT_MIN', ~PHP_INT_MAX);

include_once("Configuration.php");

date_default_timezone_set('UTC');

startSession();

include_once("org/menesty/server/functions.php");
include_once("org/menesty/server/Language.php");
include_once("org/menesty/server/Router.php");
include_once("org/menesty/server/Template.php");
include_once("org/menesty/server/Redirect.php");
include_once("org/menesty/server/Validator.php");
include_once("org/menesty/server/Browser.php");
include_once("org/menesty/server/Database.php");
include_once("org/menesty/server/Utils.php");
include_once("org/menesty/server/service/AbstractService.php");
include_once("org/menesty/server/AbstractController.php");



$router = new Router();
$router->delegate();

function startSession()
{
    include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "ShoppingCart.php");
    session_start();
    ShoppingCart::init();
}