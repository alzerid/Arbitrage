<?
//TODO: Move controller type methods to the controller
//TODO: Auto load models

class CApplication implements IExceptionListener, ISingleton
{
	static private $_VERSION = "2.0.0";
	static private $_instance = NULL;    //Instance of CApplication

	private $_controller;         //The controller object that was requested
	private $_action;             //The action object that was requested
	//private $_javascripts;
	//private $_stylesheets;

	public function __construct()
	{
		$this->_controller = NULL;
		$this->_action     = NULL;
	}

	static public function getInstance()
	{
		if(self::$_instance == NULL)
			self::$_instance = new CApplication();

		return self::$_instance;
	}


	/**
	 * Loads all the required files for the framework.
	 */
	public function bootstrap()
	{
		//Check for framework path
		if(!file_exists(ARBITRAGE_FW_PATH))
			die("Framework path is not defined in environment variable ARBITRAGE_FW_PATH");

		//Load base required framework files
		$this->requireFrameworkFile('Exceptions.class.php');                       //File full of base exception classes
		$this->requireFrameworkFile('CExceptionHandler.class.php');                //Exception class that handles exceptions
		$this->requireFrameworkFile('base/CApplication.class.php');                //Main application class that drive Arbitrage
		$this->requireFrameworkFile('base/CBaseController.class.php');             //Base controller class
		$this->requireFrameworkFile('base/CController.class.php');                 //Controller class
		$this->requireFrameworkFile('base/CAction.class.php');                     //Action class
		$this->requireFrameworkFile('base/renderers/CRenderer.class.php');         //Base renderer class
		$this->requireFrameworkFile('base/renderers/CViewFileRenderer.class.php'); //View File renderer class
		$this->requireFrameworkFile('base/CFilterChain.class.php');                //Filter chain for CBaseControllers
		$this->requireFrameworkFile('CExceptionHandler.class.php');                //Exception handler
		$this->requireFrameworkFile('base/CRouter.class.php');                     //Router handler

		//Extended framework files
		$this->requireFrameworkFile('utils/URL.class.php');
		$this->requireFrameworkFile('config/CArbitrageConfig.class.php');

		//Register exception handler
		CExceptionHandler::getInstance()->addListener($this);

		//Database classes
		$this->requireFrameworkFile('db/CModel.class.php');

		//HTML
		$this->requireFrameworkFile('base/CHTMLComponent.class.php');
		$this->requireFrameworkFile('form/Form.class.php');
		
		//Autoload model handler
		spl_autoload_register('CApplication::modelAutoLoad', true, true);
	}

	/** 
	 * Initializes the web application by loading its config file
	 * and determining which other framework classes to require.
	 * @path string The path to the application configuration file.
	 */

	public function initialize($path="config/config.php")
	{

		//Load application config
		$this->loadApplicationConfig($path);

		//Determine what db models we should load
		$databases = CArbitrageConfig::getInstance()->arbitrage->databases;
		if(count($databases))
		{
			foreach($databases as $db => $vals)
			{
				if(strtolower($db) === "mongo")
				{
					$this->requireFrameworkFile("db/CMongoModel.class.php");
				}
				else
					throw new EArbitrageConfigException("Unknown database type '$db' in configuration file.");
			}
		}
		
		//Remote cache
		$this->requireFrameworkFile("cache/remote/CRemoteCacheFactory.class.php");
		CRemoteCacheFactory::initialize(CArbitrageConfig::getInstance()->arbitrage->remoteCache);

		//TODO: Local cache loading
		
		//Other stuff
		$this->requireFrameworkFile('utils/XMLDomConstruct.class.php');
		$this->requireFrameworkFile('utils/Curl.class.php');
		$this->requireFrameworkFile('utils/TemplateFile.class.php');
		$this->requireFrameworkFile('utils/HelperFunctions.php');




		//What to do with these guys?
		/*require_once($config->fwrootpath . 'include/FastLog.class.php');
		require_once($config->fwrootpath . 'include/Error.class.php');
		require_once($config->fwrootpath . 'include/ReturnMedium.class.php');
		require_once($config->fwrootpath . 'include/ArrayManipulator.class.php');
		require_once($config->fwrootpath . 'lib/database/MongoFactory.class.php');
		require_once($config->fwrootpath . 'lib/database/DB.class.php');
		Application::requireFrameworkFile('base/CArbitrageException.class.php');
		Application::requireFrameworkFile('config/CArbitrageConfig.class.php');
		Application::requireFrameworkFile('cache/CLocalCacheFactory.class.php');
		Application::requireFrameworkFile('logging/LogFacility.class.php');*/
	}

	public function run()
	{
		//TODO: Add a primary buffer layer then flush it
		//Parse URL and grab correct route
		$route = CRouter::route($_SERVER['REQUEST_URI']);

		//Get API class from Router
		$this->loadController($route);
			
		//Execute the action
		$this->_controller->execute();
	}

	/**
	 * Method loads the controller into memory and the action into memory.
	 * $param $controller The controller in route format to load.
	 */
	public function loadController($route)
	{
		$route = explode("/", $route);

		//Throw error if route is malformed
		if(count($route) < 2)
			throw new EArbitrageException("Unable to load controller because route is malformed '" . implode('/', $route) . "'.");

		$controller = strtolower($route[0]);
		$action     = strtolower($route[1]);

		//Require controller
		$this->requireApplicationController($controller);

		//Determine if we are an ajax call
		if(isset($_GET['_ajax']))
		{
			$this->requireApplicationAjaxController($controller);
			$controller = $controller . "AjaxController";
		}
		else
			$controller = $controller . "Controller";

		//Set Application controller to the new controller
		$this->_controller = new $controller();
		$this->_action     = new CAction($this->_controller, $route[1]);
		$this->_controller->setAction($this->_action);
	}

	/** 
	 * Loads the application configuration file.
	 * @path string The path to the application configuration file.
	 */
	public function loadApplicationConfig($path="config/config.php")
	{
		if(file_exists($path))
			require_once($path);
		else
			throw new EArbitrageException("Unable to load application configuration file.");
	}

	public function requireFrameworkFile($file)
	{
		require_once(ARBITRAGE_FW_PATH . "framework/$file");
	}

	public function requireApplicationController($controller)
	{
		//Load controller into memory
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/controllers/" . $controller . ".php";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to load controller '$controller' because it does not exist.");

		require_once($path);
	}

	public function requireApplicationAjaxController($controller)
	{
		//Load controller into memory
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/ajax/" . $controller . ".php";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to load ajax controller '$controller' because it does not exist.");

		require_once($path);
	}

	public function requireApplicationModel($model)
	{
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/models/$model.php";
		if(!file_exists($path))
			throw new EArbitrageException("Model '$model' does not exist.");

		require_once($path);
	}

	static public function getConfig()
	{
		return CArbitrageConfig::getInstance();
	}

	static public function modelAutoLoad($class_name)
	{
		if(preg_match('/Model/', $class_name))
		{
			$class = strtolower(str_replace("Model", "", $class_name));
			CApplication::getInstance()->requireApplicationModel($class);
		}
	}



	/* Exception Listner Methods */
	public function handleException(Exception $ex)
	{
		//TODO: Handle $ex->getPrevious exceptions and show them
		//TODO: Get error handler controller and post the error

		//Grab exception handler
		var_dump(get_class($ex));
		var_dump($ex);
		die("CODE ME: CApplication::handleException");
	}
	/* End Exception Listner Methods */














	/*static function generateJavascriptLink($file)
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
		return CArbitrageConfig::getInstance();
	}

	static public function getPublicHtmlFile($file)
	{
		global $_conf;

		$path = $_conf['approotpath'] . "public/html/$file";
		if(!file_exists($path))
			return '';

		return file_get_contents($path);
	}

	static public function requireApplicationLibrary($path)
	{
		$path = Application::getConfig()->approotpath . "app/lib/$path";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to include application library '$path'!");

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
			throw new #ArbitrageException("Unable to include controller '$filename'.");

		require_once($file);
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
	}*/
}
?>
