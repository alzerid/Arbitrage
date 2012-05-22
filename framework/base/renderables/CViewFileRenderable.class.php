<?
class CViewFileRenderable implements IViewFileRenderable
{
	protected $_view_variables;
	protected $_layout_path;
	protected $_view_path;
	protected $_default_layout;
	protected $_renderable;

	public function __construct()
	{
		//setup view and layout paths
		$this->setLayoutPath();
		$this->setViewPath();
		$this->setDefaultLayout();

		$this->_renderable    = array();
		$this->_view_variables = array();
	}

	public function setDefaultLayout($layout="default")
	{
		$this->_default_layout = $layout;
	}

	public function setLayoutPath($path=NULL)
	{
		$this->_layout_path = (($path === NULL)? CApplication::getConfig()->_internals->approotpath . "app/views/layout/" : $path);
	}

	public function setViewPath($path=NULL)
	{
		$this->_view_path = (($path === NULL)? CApplication::getConfig()->_internals->approotpath . "app/views/" : $path);
	}

	public function getDefaultLayout()
	{
		return $this->_default_layout;
	}

	public function getLayoutPath()
	{
		return $this->_layout_path;
	}

	public function getViewPath()
	{
		return $this->_view_path;
	}

	public function render()
	{
		//Get content from view
		$content = $this->renderPartial($this->_renderable['render'], $this->_view_variables);

		//Now render layout
		$path = $this->_layout_path . $this->_renderable['layout'] . ".php";
		if(!file_exists($path))
			throw new EArbitrageException("Layout does not exist '$path'.");

		//Extract the variables
		$_vars = $this->_view_variables;
		extract($_vars);

		//Require view
		require_once($path);

		return ob_get_clean();
	}

	public function renderPartial($file, $variables=NULL)
	{
		$_vars = $variables;
		if($_vars !== NULL)
			extract($_vars);

		//Generate file path
		$path = $this->_view_path . $file . ".php";
		
		if(!file_exists($path))
			throw new EArbitrageException("View file does not exist '$path'.");

		ob_start();
		ob_implicit_flush(false);
		require($path);
		$content = ob_get_clean();

		return $content;

	}
}
?>
