<?
class Application 
{
	protected $_get;
	protected $_post;
	protected $_cookie;
	protected $_session;
	protected $_files;

	protected $_controller_name;
	protected $_action_name;

	static private $_javascripts = array();
	static private $_stylesheets = array();

	private $_modules;
	private $_components;

	public function __construct()
	{
		$this->_get = $_GET;
		unset($this->_get['_route']);

		$this->_post     = $_POST;
		$this->_cookie   = $_COOKIE; 
		
		if(isset($_SESSION))
			$this->_session =& $_SESSION;
		else
			$this->_session = NULL;

		$this->_files = ((isset($_FILES))? $_FILES : array());

		$this->_modules    = array();
		$this->_components = array();
	}

	static function includeJavascriptFile($file)
	{
		self::$_javascripts[] = $file;
	}

	static function includeStylesheetFile($file)
	{
		self::$_stylesheets[] = $file;
	}

	static public function populateJavascriptTags()
	{
		$ret = '';
		if(count(self::$_javascripts))
		{
			foreach(self::$_javascripts as $js)
				$ret .= "<script language='JavaScript' src='$js'></script>\n";
		}

		return $ret;
	}

	static public function populateStylesheetTags()
	{
		$ret = '';
		if(count(self::$_stylesheets))
		{
			foreach(self::$_stylesheets as $css)
				$ret .= "<link rel='stylesheet' type='text/css' href='$css' />\n";
		}

		return $ret;
	}

	static public function initialize()
	{
		//Initialize Components
		//Application::initializeComponenets();

		//Initialize Modules
		//Application::initializeModules();
	}

	public function getModule($name)
	{
		global $_conf;

		$name = strtolower($name);
		if(!isset($this->_modules[$name]))
		{
			//Require the module
			require_once($_conf['approotpath'] . "modules/{$name}/$name.php");

			$module = $name . "Module";
			$module = new $module($name, $this->_controller_name, $this->_action_name);
			$this->_modules[$name] = $module;
			$this->_modules[$name]->_controller_name = $this->_controller_name;
			$this->_modules[$name]->_action_name     = $this->_action_name;
		}

		return $this->_modules[$name];
	}

	public function getComponent($name)
	{
		global $_conf;

		//Check for component
		$name = strtolower($name);
		if(!isset($this->_components[$name]))
		{
			//Require the component
			require_once($_conf['approotpath'] . "components/$name.php");

			$component = $name . "Component";
			$component = new $component;
			$this->_components[$name] = $component;
		}

		return $this->_components[$name];
	}
}
?>
