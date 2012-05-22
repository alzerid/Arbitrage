<?
/**
 * Controller Arbitrage Class
 * @author Eric M. Janik
 * @version 2.0
 */

class CController extends CBaseController
{
	//Tags
	private $_javascripts;
	private $_stylesheets;

	public function __construct()
	{
		parent::__construct();

		//Set other variables
		$this->_javascripts    = array();
		$this->_stylesheets    = array();
	}

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
}
?>
