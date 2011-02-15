<?php

require_once('{COCAINE_DIRECTORY}/bootstrap.php');
require_once($_conf['fsfwpath'] . 'include/API.class.php');
require_once($_conf['fsfwpath'] . 'include/XMLDomConstruct.class.php');
require_once($_conf['fsfwpath'] . 'include/Router.class.php');
require_once($_conf['fsfwpath'] . 'include/FastLog.class.php');
require_once($_conf['fsfwpath'] . 'lib/common/HelperFunctions.php');
require_once($_conf['fsfwpath'] . 'include/Error.class.php');
require_once($_conf['fsfwpath'] . 'include/ReturnMedium.class.php');
require_once($_conf['fsfwpath'] . 'lib/distributed_cache/Cache.class.php');
require_once($_conf['fsfwpath'] . 'lib/database/MongoFactory.class.php');
require_once($_conf['fsfwpath'] . 'lib/database/DB.class.php');
require_once($_conf['fsfwpath'] . 'include/dao.php');
require_once($_conf['fsfwpath'] . 'include/Model.php');
require_once($_conf['fsfwpath'] . 'include/Business.class.php');

//Get API class from Router
$api = Router::getAPI();

if($api == NULL)
{
  //Log the error
  FastLog::logit("core", __FILE__, "Unable to get API class.");

  //TODO: Die with an error json return
  die();
}
	
//Execute the api
$api->execute();
?>
