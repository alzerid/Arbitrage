<?
namespace Framework\Base;
class CWebPackage extends CPackage
{
	/**
	 * Initializes the web package.
	 */
	public function initialize()
	{
		parent::initialize();

		//Add Javascript routing capabilities
		$namespace = $this->getNamespace();

		//Update routes
		$routes = $this->getRootParent()->getConfig()->webApplication->routes->toArray();
		$url    = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToURL($this->getNamespace());

		//Prepend
		$new_route = array('/^\/' . preg_replace('/\//', '\/', $url) . '\/javascript\/.*.js$/' => $url . '/blog/getJavascript');

		//Update routes
		$this->getApplication()->getConfig()->webApplication->routes = array_merge($new_route, $routes);
	}

	/**
	 * Method retunrs the root URL for this package.
	 */
	public function getURL()
	{
		return CKernel::getInstance()->convertArbitrageNamespaceToURL($this->getNamespace());
	}
}
?>
