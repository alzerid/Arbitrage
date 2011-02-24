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

		$controller_name = ucfirst($controller) . "Controller";
		$controller = new $controller_name($controller, $view);

		return $controller;
	}
	
  static public function getAPI()
  {
    global $_conf;

    $route  = $_GET['_route_api'];
    $api    = dirname($route);
    $method = basename($route);

    //Find file to route to
    $file = $_conf['fsapipath'] . "$api/api.php";

    //Api does not exists, return
    if(!file_exists($file))
      return NULL;

    //include the api file
    require_once($file);

    //Construct the api object
    $api = str_replace("/", " ", $api);
    $api = ucwords($api);
    $api = str_replace(" ", "_", $api);
    $api = $api . "API";

    //Construct object
    $api = new $api($method);

    return $api;
  }
}
?>
