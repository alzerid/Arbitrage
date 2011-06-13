<?
define("COCAINE_FW_PATH", "/domain/cocaine/");

//Pre boot files that are needed before application boot
require_once(COCAINE_FW_PATH . 'include/CocaineException.class.php');
require_once(COCAINE_FW_PATH . 'include/Application.class.php');
require_once(COCAINE_FW_PATH . 'include/CocaineConfig.class.php');
require_once(COCAINE_FW_PATH . 'lib/local_cache/LocalCacheFactory.class.php');
require_once("config/config.php");

//Get configuration
$config = Application::getConfig();
require_once($config->fwrootpath . 'include/Module.class.php');
require_once($config->fwrootpath . 'include/Component.class.php');
require_once($config->fwrootpath . 'include/HTMLComponent.class.php');
require_once($config->fwrootpath . 'include/Controller.class.php');
require_once($config->fwrootpath . 'include/Model.class.php');
require_once($config->fwrootpath . 'include/MongoModel.class.php');
require_once($config->fwrootpath . 'include/DataSet.class.php');
require_once($config->fwrootpath . 'include/Form.class.php');
require_once($config->fwrootpath . 'include/URL.class.php');
require_once($config->fwrootpath . 'include/XMLDomConstruct.class.php');
require_once($config->fwrootpath . 'include/Router.class.php');
require_once($config->fwrootpath . 'include/FastLog.class.php');
require_once($config->fwrootpath . 'lib/common/Curl.php');
require_once($config->fwrootpath . 'lib/common/HelperFunctions.php');
require_once($config->fwrootpath . 'include/Error.class.php');
require_once($config->fwrootpath . 'include/ReturnMedium.class.php');
require_once($config->fwrootpath . 'lib/distributed_cache/Cache.class.php');
require_once($config->fwrootpath . 'lib/database/MongoFactory.class.php');
require_once($config->fwrootpath . 'lib/database/DB.class.php');
?>
