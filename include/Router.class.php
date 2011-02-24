<?
class Router
{
	static public function getController()
	{
		global $_conf;
		$route  = $_GET['_route'];

		//Parse out the controller and view
		$route = explode("/", $route);
		$controller = strtolower($route[0]);
		$view       = $route[1];

		//Require once the controller
		$controller_path = $_conf['approotpath'] . "app/controllers/$controller.php";
		require_once($controller_path);

		//check to see if we are calling ajax
		if(isset($_GET['_ajax']))
		{
			//require the base controller
			$controller_path = $_conf['approotpath'] . "app/ajax/$controller.php";
			require_once($controller_path);
			$controller     .= "Ajax";
		}

		$controller_name = ucfirst($controller) . "Controller";
		$controller = new $controller_name($controller, $view);

		return $controller;
	}
	
}
?>
