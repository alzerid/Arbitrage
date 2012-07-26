<?
namespace Arbitrage2\Base;

class CJavascriptController extends CController
{
	public function initialize()
	{
		//Set renderable
		$this->setRenderable('Arbitrage2.Renderables.CJavascriptRenderable');

		//Get path and add a route for this namespace for javascript
		$namespace = $this->getPackage()->getNamespace();
		//$config = $this->getApplication()->getConfig();
		var_dump($namespace);
		die();
	}

	public function processRequestAction()
	{
		die('process request action');
	}
}
?>
