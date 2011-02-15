<?
//Get environment
global $_env;
$_env = "development";
if(isset($_SERVER['STAGE_ENVIRONMENT']))
	$_env = $_SERVER['STAGE_ENVIRONMENT'];
	
require_once(dirname(__FILE__) . '/../lib/local_cache/LocalCacheFactory.class.php');
require_once(dirname(__FILE__) . "/../config/config.php");
?>
