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

		//Prepend
		$new_routes = array('/^\/bootstrap\.js(\?.*)?$/i'         => 'arbitrage2/client_mvc/client/bootstrap',
		                    '/^\/client_mvc\/javascript\/.*.js$/' => 'arbitrage2/client_mvc/client/javascript');

		//Update routes
		$this->getRootParent()->getConfig()->webApplication->routes = array_merge($new_routes, $array);


		//Add bootstrap.js and arbitrage javascript tags
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/bootstrap.js?action=' . $this->getRootParent()->getURI()));
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/client_mvc/javascript/base/arbitrage2.js'));

		//require javascript file defined by user
		if(isset($this->getConfig()->applicationScript))
			\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => $this->getConfig()->applicationScript));
	}
}

?>
