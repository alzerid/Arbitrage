<?
namespace Arbitrage2\Renderables;
use \Arbitrage2\Interfaces\IViewFileRenderable;
use \Arbitrage2\Base\CApplication;

class CViewFilePartialRenderable implements IViewFileRenderable
{
	static protected $_VIEW_PATHS = array();

	public function __construct()
	{
		$this->addViewPath(CApplication::getConfig()->_internals->approotpath . "app/views/");
	}

	static public function getViewPaths()
	{
		return self::$_VIEW_PATHS;
	}

	static public function addViewPath($path)
	{
		self::$_VIEW_PATHS[] = $path;
	}

	static public function setViewPath($path)
	{
		self::$_VIEW_PATHS = array($path);
	}

	public function render($data=NULL)
	{
		//Setup data
		$default = array('render'    => CApplication::getInstance()->getController()->getName() . "/" . CApplication::getInstance()->getController()->getAction()->getName(),
		                 'variables' => array());

		//Merge defaults with data
		$default['render']    = ((isset($data['render']))? $data['render'] : $default['render']);
		$default['variables'] = array_merge($default['variables'], (isset($data['variables'])? $data['variables'] : array()));

		//Get content from view
		return $this->renderPartial($default['render'], $default['variables']);
	}

	public function renderPartial($file, $variables=NULL)
	{
		$_vars = $variables;
		if($_vars !== NULL)
			extract($_vars);

		$_controller = CApplication::getInstance()->getController();
		$_action     = CApplication::getInstance()->getController()->getAction();

		//Get view file
		$path = NULL;
		foreach(self::$_VIEW_PATHS as $vp)
		{
			if(file_exists($vp . "/" . $file . ".php"))
			{
				$path = $vp . "/" . $file . ".php";
				break;
			}
		}
		
		if($path == NULL)
		{
			die();
			throw new EArbitrageException("View file does not exist '$file'.");
		}

		ob_start();
		ob_implicit_flush(false);
		require($path);
		$content = ob_get_clean();

		return $content;
	}
}

?>
