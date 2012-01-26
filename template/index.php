<?php
try
{
	require_once('bootstrap.php');

	//Parse URL and grab correct route
	$route = Router::route($_SERVER['REQUEST_URI']);

	//Get API class from Router
	$controller = Router::getController($route);
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

		case "View": 
			//Show 404
			header("Status: 404 Not Found");
			$html = Application::getPublicHtmlFile('404.html');
			echo $html;
			echo "<!-- $ex -->";

			//Add to tmp file
			$body = "START(" . date("Y/m/d H:i:s") . "):\n$ex\n:END\n";
			file_put_contents("/tmp/af_404.txt", $body, FILE_APPEND);
			die();
			break;
	}
	//Handle exception
	echo "HANDLE EXCEPTION<br/>";
	echo $ex->getMessage();
}
?>
