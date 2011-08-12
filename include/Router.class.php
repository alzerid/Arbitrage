<?
class Router
{
	static public function getController()
	{
		$conf = Application::getConfig();

		if(trim($_GET['_route']) == '')
		{
			//Attach get string
			$get = '';
			unset($_GET['_route']);
			if(isset($_GET) && count($_GET))
				$get = '?' . http_build_query($_GET);

			if(isset($conf->routing['_default']))
			{
				header("Location: {$conf->routing['_default']}$get");
				die();
			}
			else
			{
				echo "Default route not set. Please set it in the routing.yaml file.";
				die();
			}
		}

		$route = $_GET['_route'];

		//Parse out the controller and view
		$route      = explode("/", $route);
		$controller = strtolower($route[0]);
		$view       = $route[1];

		//Require once the controller
		$controller_path = $conf->approotpath . "app/controllers/$controller.php";
		if(!file_exists($controller_path))
			throw new CocaineException('Unable to load route ' . implode('/', $route) . '.');

		require_once($controller_path);

		//Do some advanced routing logic
		$routing = $conf->routing;
		if(isset($routing[$controller]))
		{
			foreach($routing[$controller] as $match => $val)
			{
				if(preg_match($match, $view))
				{
					$view = $val;
					break;
				}
			}
		}

		//check to see if we are calling ajax
		$ajax = false;
		if(isset($_GET['_ajax']))
		{
			//require the base controller
			$controller_path = $conf->approotpath . "app/ajax/$controller.php";
			require_once($controller_path);
			$controller     .= "Ajax";
			$ajax            = true;
		}

		//Get controller and view
		$controller_name = ucfirst($controller) . "Controller";
		$controller = new $controller_name($controller, $view);
		$controller->setAjax($ajax);

		return $controller;
	}
}
?>
