<?
define('APP_VERSION', 1.0);
define('APP_XML_NAME', 't3api');
define('CONFIG_CACHE', false);
define('CONFIG_CACHE_VEHICLE', 'apc');

date_default_timezone_set('America/Chicago');

/* Error reporting */
error_reporting(E_ALL);

/*****************************/
/*** Configuration Section ***/
/*****************************/
global $_conf;
global $_env;

//Get local cache
$lcache = LocalCacheFactory::initLocalCache(CONFIG_CACHE_VEHICLE);
$_conf  = ((CONFIG_CACHE)? $lcache->get("_conf_{$_env}") : NULL);

if($_conf == NULL)
{
  //Config base
	$realpath = realpath(dirname(realpath(__FILE__)) . "/../../");
	$_conf['fsrootpath']   = $realpath . "/";
	$_conf['urlrootpath']  = '/';
	$_conf['fsfwpath']     = "$realpath/framework/";
	$_conf['fsapppath']    = "$realpath/app/";
	$_conf['fsapipath']    = "$realpath/app/api/";

	//Pick the environment to include files
	$path   = dirname(__FILE__) . "/$_env";

  //Database config
  require_once($path . '/config_db.php');

  //Distributed cache
  require_once($path . '/config_cache.php');
  
  //Cake Specific Configuration
  require_once($path . '/config_cake.php');

  //Set to local cache
	if(CONFIG_CACHE)
  	$lcache->set("_conf_{$_env}", $_conf, 720);
}
?>
