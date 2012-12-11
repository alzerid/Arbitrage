<?
namespace Framework\Packages\ArbitrageClient;

class CArbitrageClientPackage extends \Framework\Base\CWebPackage
{
	private $_includes;

	public function initialize()
	{
		$url = $this->getURL();

		//Initialize include paths
		$this->_includes = array('arbitrage2' => "/$url/javascript");

		//Initialize the package
		parent::initialize();

		//Add bootstrap route
		$this->addRoute('/^\/bootstrap\.js(\?.*)?$/i', $url . '/client/bootstrap');

		//Initialize JS routing
		$this->initializeJavascript('client');

		//Add arbitrage javascript
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/bootstrap.js'));
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => "/$url/javascript/arbitrage2/base/arbitrage2.js"));
	}

	/**
	 * @return array Returns the include url paths.
	 */
	public function getIncludePaths()
	{
		return $this->_includes;
	}

	/**
	 * @param string $namespace The first part of the namespace to bind to a path.
	 * @param string $path The JS path to use.
	 */
	public function addIncludePath($namespace, $path)
	{
		$this->_includes[$namespace] = $path;
	}
}
?>
