<?
namespace Framework\ArbitrageClient;

class CArbitrageClientPackage extends \Framework\Base\CPackage
{
	private $_includes;

	public function initialize()
	{
		//Initialize include paths
		$this->_includes = array();

		//Initialize the package
		parent::initialize();

		//Add bootstrap route
		$route = array('/^\/bootstrap\.js(\?.*)?$/i' => 'framework/arbitrage_client/client/bootstrap');
		$this->getApplication()->getConfig()->webApplication->routes = array_merge($this->getApplication()->getConfig()->webApplication->routes->toArray(), $route);

		//Initialize JS routing
		parent::initializeJavascript('client');

		//Add arbitrage javascript
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/bootstrap.js?action=' . $this->getApplication()->getVirtualURI()));
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/framework/arbitrage_client/javascript/arbitrage2/base/arbitrage2.js'));
	}

	/**
	 * @param string $namespace The first part of the namespace to bind to a path.
	 * @param string $path The JS path to use.
	 */
	public function addIncludePath($namespace, $path)
	{
		$this->_includes[$namespace] = $path;
	}

	/**
	 * @param array $config The configuration array to append.
	 */
	
}
?>
