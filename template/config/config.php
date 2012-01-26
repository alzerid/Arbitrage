<?
define('APP_VERSION', 1.0);
define('CONFIG_CACHE', false);
define('CONFIG_CACHE_VEHICLE', 'apc');

date_default_timezone_set('America/Chicago');

/* Error reporting */
error_reporting(E_ALL);

/*****************************/
/*** Configuration Section ***/
/*****************************/
$env = ((isset($_SERVER['ARBITRAGE_ENVIRONMENT']))? $_SERVER['ARBITRAGE_ENVIRONMENT'] : 'development');

$config = Application::getConfig();
$config->initialize(realpath(dirname(__FILE__) . "/../config"), $env);
$config->load('arbitrage.yml');
$config->load('routing.yml');

//Get varliabes, compatibility for LIB folder in arbitrage
global $_conf;
$_conf = & $config->getVariables();
?>
