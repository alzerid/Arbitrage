<?
namespace Framework\ClientMVC\Controllers;

class ClientController extends \Arbitrage2\Base\CJavascriptController
{
	public function bootstrapAction()
	{
		$routes = $this->getPackage()->getConfig()->routes->toArray();
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
		$config = $this->getPackage()->getConfig()->toArray();
		unset($config['routes']);
		$config = array_merge($config, array('debug' => $this->_application->getConfig()->arbitrage2->debugMode));
		$config['mvc']['routing'] = $global;

		//Get JSON
		$config = json_encode($config);
		$js     = "var arbitrage2 = { config: $config };";

		return array('render' => $js);
	}

	public function javascriptAction()
	{
		$path = $this->getPackage()->getPath() . "/" . \Arbitrage2\Base\CKernel::getInstance()->convertArbitrageNamespaceToPath($this->getPackage()->getNamespace() . '.null');
		$path = preg_replace('/.null/', '', $path) . preg_replace('/\/client_mvc/i', '', $_SERVER['REQUEST_URI']);

		//Check if exists
		if(!file_exists($path))
			throw new \Arbitrage2\Exceptions\EHTTPException(\Arbitrage2\Exceptions\EHTTPException::$HTTP_BAD_REQUEST);

		return array('render' => file_get_contents($path));
	}
}
?>
