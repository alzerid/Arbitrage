<?
namespace Arbitrage2\ClientMVC;

class CClientMVCService extends \Arbitrage2\Base\CService
{
	static public $_SERVICE_TYPE = "clientMVC";

	public function initialize()
	{
		//Make sure other services we depend on is loaded
		//$this->depends('Arbitrage2.');
	
		//Get config and update routing
		$routes = $this->getApplication()->getConfig()->webApplication->routes;
		$array  = $routes->toArray();

		//Prepend
		$array = array_merge(array('/^\/bootstrap\.js$/i' => 'arbitrage2/client_mvc/client/bootstrap'), $array);
		$this->getApplication()->getConfig()->webApplication->routes = $array;

		//Register path
		\Arbitrage2\Base\CKernel::getInstance()->registerPackagePath(dirname(dirname(dirname(__FILE__))));

		//Add bootstrap.js and arbitrage javascript tags
		\Arbitrage2\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/bootstrap.js?action=' . $this->getApplication()->getURI()));

		//require javascript file defined by user
		if(isset($this->getConfig()->applicationScript))
			\Arbitrage2\DOM\CDOMGenerator::addJavascriptTag(array('src' => $this->getConfig()->applicationScript));
	}
}
?>
