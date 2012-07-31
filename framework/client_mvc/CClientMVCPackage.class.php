<?
namespace Framework\ClientMVC;

class CClientMVCPackage extends \Framework\Base\CPackage
{
	public function initialize()
	{
		parent::initialize();

		//Get config and update routing
		$routes = $this->getRootParent()->getConfig()->webApplication->routes;
		$array  = $routes->toArray();
		$app    = $this->getApplication();

		//Prepend
		$new_routes = array('/^\/bootstrap\.js(\?.*)?$/i'                    => 'framework/client_mvc/client/bootstrap',
		                    '/^\/framework\/client_mvc\/javascript\/.*.js$/' => 'framework/client_mvc/client/getJavascript');

		//Update routes
		$app->getConfig()->webApplication->routes = array_merge($new_routes, $array);

		//Add bootstrap.js and arbitrage javascript tags
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/bootstrap.js?action=' . $app->getVirtualURI()));
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/framework/client_mvc/javascript/arbitrage2/base/arbitrage2.js'));

		//require javascript file defined by user
		$namespace = preg_replace('/^\/?([^\/]+)\/?.*$/', '$1' , $app->getVirtualURI());
		$path      = $this->getConfig()->includePaths[$namespace];
		if(isset($path))
		{
			$path = $path . "/" . $namespace . "/application.js";
			$path = preg_replace('/[\\/]+/', '/', $path); //Remove double '/'
			\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => $path));
		}
	}
}
?>
