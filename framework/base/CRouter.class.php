<?
class CRouter
{
	static public function route($url)
	{
		//Remove Query strings
		$url = preg_replace('/\?.*$/', '', $url);
		
		$routes = CApplication::getConfig()->routing;
		if(isset($routes))
		{
			foreach($routes as $key=>$route)
			{
				$matches = array();
				if(preg_match($key, $url, $matches))
				{
					//String replace
					if(count($matches) > 1)
					{
						$replace = array_slice($matches, 1);
						foreach($replace as $pos => $val)
						{
							$idx    = ($pos+1);
							$search = '/(\\\|\$)' . $idx . '/';
							$route = preg_replace($search, $val, $route);
						}
					}

					//Break from main loop cause we found our url
					$url = $route;
					break;
				}
			}
		}

		//Remove leading /
		$url = preg_replace('/^\//', '', $url);

		return $url;
	}

	static public function getController($route=NULL)
	{
		$conf = Application::getConfig();

		if($route == NULL)
			$route = $_GET['_route'];

		//Parse out the controller and view
		$route      = explode("/", $route);
		$controller = strtolower($route[0]);
		$view       = ((count($route) >=2)? $route[1] : '');

		//Require once the controller
		$controller_path = $conf->approotpath . "app/controllers/$controller.php";
		if(!file_exists($controller_path))
			throw new ArbitrageException('Unable to load route ' . implode('/', $route) . '.');

		require_once($controller_path);

		//check to see if we are calling ajax
		$ajax = false;
		if(isset($_GET['_ajax']) || isset($_POST['_ajax']))
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
