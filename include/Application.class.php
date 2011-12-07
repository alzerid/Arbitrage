<?
class Application 
{
	public $pageTitle;

	protected $_get;
	protected $_post;
	protected $_cookie;
	protected $_session;
	protected $_files;

	protected $_controller_name;
	protected $_action_name;

	static private $_javascripts = array();
	static private $_stylesheets = array();
	static protected $_inlinejs  = '';
	static private $_backtrace   = '';  //Backtrace text for error displaying

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
		
		$this->pageTitle = "";

		spl_autoload_register('Application::autoload', true, true);
	}

	static function generateJavascriptLink($file)
	{
		return "<script type=\"text/javascript\" language=\"JavaScript\" src=\"$file\"></script>\n";
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
				$ret .= "<script type=\"text/javascript\" language=\"JavaScript\" src=\"$js\"></script>\n";
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

	static public function getConfig()
	{
		return ArbitrageConfig::getInstance();
	}

	static public function getPublicHtmlFile($file)
	{
		global $_conf;

		$path = $_conf['approotpath'] . "public/html/$file";
		if(!file_exists($path))
			return '';

		return file_get_contents($path);
	}

	public function getModule($name, $opts=array())
	{
		global $_conf;

		$name = strtolower($name);
		if(!isset($this->_modules[$name]))
		{
			//Require the module
			require_once($_conf['approotpath'] . "modules/{$name}/$name.php");

			$module = $name . "Module";
			$module = new $module($this, $opts);
			$this->_modules[$name] = $module;
		}

		//Set options for the module
		$this->_modules[$name]->setOptions($opts);

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
			$component->_controller_name = $this->_controller_name;
			$component->_action_name     = $this->_action_name;
			$this->_components[$name] = $component;
		}

		return $this->_components[$name];
	}

	public function startSession()
	{
		session_start();
		$this->_session =& $_SESSION;
	}

	static public function requireLibrary($name)
	{
		global $_conf;
		require_once($_conf['fwrootpath'] . "lib/$name");
	}

	static public function setBackTrace($txt)
	{
		self::$_backtrace = $txt;
	}

	static public function getBackTrace()
	{
		return self::$_backtrace;
	}

	static public function resetSession()
	{
		session_destroy();
	}
	
	static public function requireController($filename)
	{
		$config = Application::getConfig();
		$file   = "{$config->approotpath}/app/controllers/$filename";
		if(!file_exists($file))
			throw new ArbitrageException("Unable to include controller '$filename'.");

		require_once($file);
	}

	static public function autoload($class_name)
	{
		$conf = Application::getConfig();
		if(preg_match('/Model/', $class_name))
		{
			$class = strtolower(str_replace("Model", "", $class_name));
			$file  = "{$conf->approotpath}app/models/$class.php";
			if(!file_exists($file))
				throw new ArbitrageException("Unable to load model '$class_name'.");

			require_once($file);
		}
	}

	static public function getDefaultLogger()
	{
		$conf = Application::getConfig();
		$log  = $conf->arbitrage['logger'];

		if(!isset($log))
			throw new ArbitrageException("Unable to get default logger. Please set it up correctly in the config file.");

		$logger = LogFacilityFactory::getLogger($log['type'], $log['properties']);

		return $logger;
	}

	static public function recursiveGlob($pattern, $flags=0)
	{
		$files = glob($pattern, $flags);
		foreach(glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir)
			$files = array_merge($files, self::recursiveGlob($dir . '/' . basename($pattern), $flags));

		return $files;
	}

	static protected function _selectArrayValue($path, $data, $cb=NULL)
	{
		$path  = explode('.', $path);
		$cpath = $path[0];
		$path  = implode('.', array_slice($path, 1));
		$ret   = array();

		if($cpath != "" && $cpath[0] == "*")
		{
			foreach($data as $key=>$value)
			{
				$matches = array();
				if(preg_match('/:cb:(.*)$/', $cpath, $matches))
				{
					$func = $cb[$matches[1]];
					$func($key, $value);
				}

				$ret[] = self::_selectArrayValue($path, $value, $cb);
			}
		}
		elseif($cpath != "" && $cpath[0] == "$")
		{
			foreach($data as $key=>$value)
			{
				if(!isset($ret[$key]))
					$ret[$key] = array();

				$ret[$key] = self::_selectArrayValue($path, $value, $cb);
			}

			//Check for special operation
			if(strtolower($cpath) == '$sum')
			{
				foreach($ret as $key => $val)
					$ret[$key] = array_sum($ret[$key]);
			}
		}
		elseif($path != "")
			return self::_selectArrayValue($path, $data[$cpath], $cb);
		else
			return $data[$cpath];

		return $ret;
	}
}
?>
