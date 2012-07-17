<?
class CViewFileRenderable implements IViewFileRenderable
{
	protected $_view_variables;
	protected $_layout_paths;
	protected $_view_paths;
	protected $_layout;

	public function __construct()
	{
		$this->_layout_paths = array();
		$this->_view_paths   = array();

		//setup view and layout paths
		$this->addLayoutPath(CApplication::getConfig()->_internals->approotpath . "app/views/layout/");
		$this->addViewPath(CApplication::getConfig()->_internals->approotpath . "app/views/");
		$this->setLayout("default");

		//$this->_renderable    = array();
		$this->_view_variables = array();
	}

	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	public function getLayout()
	{
		return $this->_layout;
	}

	public function getLayoutPath()
	{
		return $this->_layout_paths;
	}

	public function addLayoutPath($path)
	{
		$this->_layout_paths[] = $path;
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
		                 'layout'    => $this->_layout,
		                 'variables' => array());

		//Merge defaults with data
		$default['render']    = ((isset($data['render']))? $data['render'] : $default['render']);
		$default['layout']    = ((isset($data['layout']))? $data['layout'] : $default['layout']);
		$default['variables'] = array_merge($default['variables'], (isset($data['variables'])? $data['variables'] : array()));

		//Get content from view
		$content = $this->renderPartial($default['render'], $default['variables']);

		//Now render layout
		$layout = NULL;
		foreach($this->_layout_paths as $lp)
		{
			if(file_exists($lp . "/" . $default['layout'] . ".php"))
			{
				$layout = $lp . "/" . $default['layout'] . ".php";
				break;
			}
		}

		if(!file_exists($layout))
			throw new EArbitrageException("Layout does not exist '{$default['layout']}'.");

		//Extract the variables
		$_vars = $this->_view_variables;
		extract($_vars);

		//Require view
		ob_start();
		ob_implicit_flush(false);
		require_once($layout);

		return ob_get_clean();
	}

	public function renderPartial($file, $variables=NULL)
	{
		$_vars = $variables;
		if($_vars !== NULL)
			extract($_vars);

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
