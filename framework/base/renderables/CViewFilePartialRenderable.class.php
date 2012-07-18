<?
class CViewFilePartialRenderable implements IViewFileRenderable
{
	protected $_view_paths;

	public function __construct()
	{
		$this->_view_paths = array();
		$this->addViewPath(CApplication::getConfig()->_internals->approotpath . "app/views/");
	}

	public function getViewPaths()
	{
		return $this->_view_paths;
	}

	public function addViewPath($path=NULL)
	{
		$this->_view_paths[] = $path;
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
		foreach($this->_view_paths as $vp)
		{
			if(file_exists($vp . "/" . $file . ".php"))
			{
				$path = $vp . "/" . $file . ".php";
				break;
			}
		}
		
		if($path == NULL)
			throw new EArbitrageException("View file does not exist '$file'.");

		ob_start();
		ob_implicit_flush(false);
		require($path);
		$content = ob_get_clean();

		return $content;
	}
}

?>
