<?
namespace Arbitrage2\ClientMVC\Controllers;

class ClientController extends \Arbitrage2\Base\CController
{
	public function bootstrapAction()
	{
		//Set Javascript Renderable mode
		$this->setRenderable('Arbitrage2.Renderables.CJavascriptRenderable');

		$config = $this->_application->getConfig()->arbitrage2->services->clientMVC['Arbitrage2.ClientMVC.CClientMVCService'];
		$routes = $config->routes->toArray();
		$global = $routes['_global'];
		unset($routes['_global']);

		//Config
		if(isset($this->_request['action']) && count($routes) > 0)
		{
			$action = $this->_get['action'];
			$action[0] !== '/' && $action = "/$action";  //Crazy syntax, works, may take a while to get used to
			foreach($routes as $key => $route)
			{
				$key = preg_replace('/\//', '\/', $key);
				if(preg_match('/' . $key . '/', $action))
					$global = array_merge($global, $route);
			}
		}

		//Return config
		$config = $config->toArray();
		unset($config['routes']);
		$config = array_merge($config, array('debug' => $this->_application->getConfig()->arbitrage2->debugMode));
		$config['mvc']['routing'] = $global;

		//Get JSON
		$config = json_encode($config);
		$js     = "var arbitrage2 = { config: $config };";

		return array('render' => $js);
	}
}
?>
