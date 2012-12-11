<?
namespace Framework\Base;
class CWebPackage extends CPackage
{
	/**
	 * Initialize JS the package.
	 * @param string The controller to use for Javascript Return.
	 */
	public function initializeJavascript($controller)
	{
		//Create URL
		$base = CKernel::getInstance()->convertArbitrageNamespaceToURL($this->getNamespace());
		$url  = "/$base/javascript/";
		$url  = "/^" . preg_replace('/\//', '\/', $url) . ".*.js$/";

		//Create route
		$route = array($url => "$base/$controller/getJavascript");
		$this->getApplication()->getConfig()->webApplication->routes = array_merge($this->getApplication()->getConfig()->webApplication->routes->toArray(), $route);
	}

	/**
	 * Method returns the root URL for this package.
	 */
	public function getURL()
	{
		return CKernel::getInstance()->convertArbitrageNamespaceToURL($this->getNamespace());
	}

	/**
	 * Method adds a route rule to the routing logic.
	 * @param $match The URL to match.
	 * @param $url The url to route to.
	 * @param $opt_prepend Optional parameter indiciating if we should prepend the route.
	 */
	public function addRoute($match, $url, $prepend=false)
	{
		$routes = $this->getApplication()->getRoutes()->toArray();

		//Check if we are prepending or not
		if($prepend)
			$routes = array_merge(array($match => $url), $routes);
		else
			$routes = array_merge($routes, array($match => $url));

		//Set new routes
		$this->getApplication()->getRoutes()->setData($routes);
	}
}
?>
