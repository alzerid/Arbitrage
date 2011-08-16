<?php
session_start();

try
{
	require_once('bootstrap.php');

	//Get API class from Router
	$controller = Router::getController();
	if($controller == NULL)
	{
		//Log the error
		FastLog::logit("core", __FILE__, "Unable to get Controller class.");

		//TODO: Die with an error json return
		die();
	}
		
	//Execute the api
	$controller->execute();
}
catch(Exception $ex)
{
	//Check how we will be displaying this error
	$conf = Application::getConfig();
	$type = $conf->arbitrage['exception_handler']['type'];
	switch($type)
	{
		case "ReturnMedium":
			$rm = new ReturnMedium;
			$rm->setErrorNo($ex->getCode());
			$rm->setMessage($ex->getMessage());

			echo $rm->render();
			die();
			break;
	}
	//Handle exception
	echo "HANDLE EXCEPTION<br/>";
	echo $ex->getMessage();
}
?>
