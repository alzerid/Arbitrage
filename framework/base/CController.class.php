<?
/**
 * Controller Arbitrage Class
 * @author Eric M. Janik
 * @version 2.0
 */

abstract class CController extends CBaseController implements IFileRenderable
{
	//Render variables
	private $_layout_path;
	private $_view_path;
	private $_default_layout;

	//Tags
	private $_javascripts;
	private $_stylesheets;

	public function __construct()
	{
		parent::__construct();

		//setup view and layout paths
		$this->setLayoutPath();
		$this->setViewPath();
		$this->setDefaultLayout();

		$this->_javascripts = array();
		$this->_stylesheets = array();
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

	/* IController implementation */
	public function renderInternal(IRenderer $renderer)
	{
		return $this->renderFile($renderer->getFile(), $renderer->getLayout(), $renderer->getVariables());
	}
	/* END IController implementation */

	/* Implementation of IFileRenderable */
	public function renderFile($file, $layout, $variables)
	{
		//ob_start();
		//ob_implicit_flush(false);

		//Get content from view
		$content = $this->renderPartialFile($file, $variables);

		//Now render layout
		$path = $this->_layout_path . $layout . ".php";
		if(!file_exists($path))
			throw new EArbitrageException("Layout does not exist '$path'.");

		$_vars = $variables;
		extract($_vars);

		//Require view
		require_once($path);

		return ob_get_clean();
	}

	public function renderPartialFile($file, $variables=NULL)
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
		require_once($path);
		$content = ob_get_clean();

		return $content;
	}

	/* renderPartial
	 * Convinence method that calls CController::renderPartialFile
	 */
	public function renderPartial($file, $variables=NULL)
	{
		return $this->renderPartialFile($file, $variables);
	}
	/* END Implementation of IFileRenderable */

	/* HTML View Helper Methods */
	public function includeJavaScriptController()
	{
		$controller = strtolower($this->_controller->getName());
		$controller = preg_replace('/Controller$/i', '', $controller);

		//Include controller if available
		$path = CApplication::getConfig()->_internals->approotpath . "app/cjavascript/$controller.js";
		if(!file_exists($path))
			return;

		$this->addJavaScriptTag("/cjavascript/$controller.js");
	}

	public function addJavaScriptTag($link)
	{
		$this->_javascripts[] = $link;
	}

	public function generateJavaScriptTag($link)
	{
		return '<script type="text/javascript" language="JavaScript" src="' . $link . '"></script>' . "\n";
	}

	public function populateJavaScriptTags()
	{
		$ret = "";
		if(count($this->_javascripts))
		{
			foreach($this->_javascripts as $js)
				$ret .= $this->generateJavaScriptTag($js) . "\n";
		}

		return $ret;
	}

	public function addStyleSheetTag($link)
	{
		$this->_stylesheets[] = $link;
	}

	public function generateStyleSheetTag($link)
	{
		return '<link type="text/css" rel="stylesheet" href="' . $link . '" />' . "\n";
	}

	public function populateStyleSheetTags()
	{
		$ret = "";
		if(count($this->_stylesheets))
		{
			foreach($this->_stylesheets as $st)
				$ret .= $this->generateStyleSheetTag($st);
		}
	
		return $ret;
	}
	/* END HTML View Helper Methods */














	/*public function getViewVariable($key)
	{
		return ((isset($this->_view_vars[$key]))? $this->_view_vars[$key] : NULL);
	}

	public function getViewVariables()
	{
		return $this->_view_vars;
	}

	public function addViewVariables($vars)
	{
		$this->_view_vars = array_merge($this->_view_vars, $vars);
	}*/
}
?>
