<?
//TODO: MOve controller type methods to the controller
class ArbitrageApplication 
{
	static private $_controller;  //The controller object that was requested
	static private $_action;      //The action object that was requested

	static private $_javascripts = array();
	static private $_stylesheets = array();
	static private $_backtrace   = '';  //Backtrace text for error displaying

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
		
		spl_autoload_register('Application::autoload', true, true);
	}

	static public function init()
	{
	}

	static public function runApplication()
	{
		//TODO: Add a primary buffer layer then flush it
		try
		{
			//Parse URL and grab correct route
			$route = Router::route($_SERVER['REQUEST_URI']);

			//Get API class from Router
			$controller = Router::getController($route);
				
			//Execute the action
			ob_start();
			$controller->execute();
		}
		catch(ArbitrageRenderException $ex)
		{
			$ex->render();
		}
		/*catch(Exception $ex)
		{
			//Show 404
			header("Status: 404 Not Found");
			$html = Application::getPublicHtmlFile('404.html');
			echo $html;
			echo "<!-- $ex -->";

			//Add to tmp file
			$body = "START(" . date("Y/m/d H:i:s") . "):\n$ex\n:END\n";
			file_put_contents("/tmp/af_404.txt", $body, FILE_APPEND);
			die();
			break;
		}*/
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

	public function startSession()
	{
		session_start();
		$this->_session =& $_SESSION;
	}

	static public function requireApplicationLibrary($path)
	{
		$path = Application::getConfig()->approotpath . "app/lib/$path";
		if(!file_exists($path))
			throw new ArbitrageException("Unable to include application library '$path'!");

		require_once($path);
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
