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

	/* Bootstrap JS for arbitrage2.mvc */
	public function bootstrapJSAction()
	{
		$this->setRendererType('renderable'); //Ensure we are in renderable render mode

		$config = CApplication::getConfig();
		$routes = $config->client->mvc->routing->toArray();
		$global = $routes['_global'];
		unset($routes['_global']);

		//Config
		if(isset($this->_get['action']) && count($routes) > 0)
		{
			$action = $this->_get['action'];
			$action[0] !== '/' && $action = "/$action";  //Crazy syntax, works, may take a while to get used to
			foreach($routes as $key => $route)
			{
				$key = preg_replace('/\//', '\/', $key);
				if(preg_match('/' . $key . '/', $action))
					$global = array_merge($global, $route);
			}
		}

		$config = array_merge($config->client->toArray(), array('debug' => $config->server->debugMode));
		$config['mvc']['routing'] = $global;

		//Get JSON
		$config = json_encode($config);
		$js     = "var arbitrage2 = { config: $config };";

		//New JSON renderable
		$json = new CJavascriptRenderable($js);
		return $json;
	}

	/* HTML View Helper Methods */
	public function includeJavascriptLayout()
	{
		$layout = $this->getLayout();
		$config = CApplication::getInstance()->getConfig();
		$path   = $config->_internals->approotpath . "public/javascript/{$config->client->mvc->rootNamespace}/layouts/$layout.js";
		if(!file_exists($path))
			return;

		$this->addJavaScriptTag("/javascript/{$config->client->mvc->rootNamespace}/layouts/$layout.js");
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
