<?
namespace Framework\Base;

class CJavascriptController extends CController
{
	public function initialize()
	{
		//Set renderable
		$this->setRenderable('Framework.Renderables.CJavascriptRenderable');
	}

	public function getJavascriptAction()
	{
		//Get path and namespace
		$path = $this->getPackage()->getPath(). $_SERVER['REQUEST_URI'];

		//Check if exists
		if(!file_exists($path))
			throw new \Framework\Exceptions\EHTTPException(\Framework\Exceptions\EHTTPException::$HTTP_BAD_REQUEST);

		return array('render' => file_get_contents($path));
	}
}
?>
